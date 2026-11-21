<?php

function getFeaturedProducts(int $limit = 6): array
{
    $pdo = db_connect();

    $sql = "SELECT sp.id, sp.ten_san_pham, sp.gia_goc, sp.mo_ta, dm.ten_danh_muc, dm.loai_mon
            FROM san_pham sp
            INNER JOIN danh_muc dm ON sp.danh_muc_id = dm.id
            WHERE sp.con_ban = 1 AND dm.con_ban = 1
            ORDER BY sp.id ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function getAllCategories(): array
{
    $pdo = db_connect();

    $sql = "SELECT id, ten_danh_muc, loai_mon
            FROM danh_muc
            WHERE con_ban = 1
            ORDER BY id ASC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getFilteredProducts(?int $categoryId = null, string $keyword = ''): array
{
    $pdo = db_connect();

    $sql = "SELECT sp.id, sp.danh_muc_id, sp.ten_san_pham, sp.gia_goc, sp.mo_ta,
                   dm.ten_danh_muc, dm.loai_mon
            FROM san_pham sp
            INNER JOIN danh_muc dm ON sp.danh_muc_id = dm.id
            WHERE sp.con_ban = 1 AND dm.con_ban = 1";

    $params = [];

    if ($categoryId !== null && $categoryId > 0) {
        $sql .= " AND sp.danh_muc_id = :category_id";
        $params[':category_id'] = $categoryId;
    }

    $trimmedKeyword = trim($keyword);
    if ($trimmedKeyword !== '') {
        $sql .= " AND (sp.ten_san_pham LIKE :keyword OR sp.mo_ta LIKE :keyword)";
        $params[':keyword'] = '%' . $trimmedKeyword . '%';
    }

    $sql .= " ORDER BY dm.id ASC, sp.id ASC";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === ':category_id') {
            $stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    $stmt->execute();

    return $stmt->fetchAll();
}
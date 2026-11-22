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

function getFilteredProducts(?int $categoryId, string $keyword = ''): array
{
    $pdo = db_connect();

    $sql = "SELECT p.*, d.ten_danh_muc
            FROM san_pham p
            LEFT JOIN danh_muc d ON p.danh_muc_id = d.id
            WHERE p.con_ban = 1";
    $params = [];

    if ($categoryId !== null && $categoryId > 0) {
        $sql .= " AND p.danh_muc_id = :category_id";
        $params[':category_id'] = $categoryId;
    }

    $keyword = trim($keyword);
    if ($keyword !== '') {
        $sql .= " AND (p.ten_san_pham LIKE :kw_name)";
        $like = '%' . $keyword . '%';
        $params[':kw_name'] = $like;
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

<?php

function getAllIngredients(string $keyword = '', string $stockFilter = 'all'): array
{
    $pdo = db_connect();
    $sql = "SELECT id, ma_nguyen_lieu, ten_nguyen_lieu, don_vi_tinh,
                   ton_kho_hien_tai, nguong_canh_bao, trang_thai, ngay_tao
            FROM nguyen_lieu
            WHERE 1=1";
    $params = [];

    $kw = trim($keyword);
    if ($kw !== '') {
        $sql .= " AND (ma_nguyen_lieu LIKE :kw_code OR ten_nguyen_lieu LIKE :kw_name)";
        $like = '%' . $kw . '%';
        $params[':kw_code'] = $like;
        $params[':kw_name'] = $like;
    }

    if ($stockFilter === 'het_hang') {
        $sql .= " AND ton_kho_hien_tai <= 0";
    } elseif ($stockFilter === 'sap_het') {
        $sql .= " AND ton_kho_hien_tai > 0 AND ton_kho_hien_tai <= nguong_canh_bao";
    }

    $sql .= " ORDER BY (ton_kho_hien_tai <= 0) DESC, (ton_kho_hien_tai <= nguong_canh_bao) DESC, ten_nguyen_lieu ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function saveIngredient(array $data, int $id = 0): void
{
    $pdo = db_connect();
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE nguyen_lieu
             SET ma_nguyen_lieu = :ma, ten_nguyen_lieu = :ten, don_vi_tinh = :dvt,
                 nguong_canh_bao = :nguong, trang_thai = :trang_thai
             WHERE id = :id"
        );
        $stmt->execute([
            ':ma' => $data['ma_nguyen_lieu'],
            ':ten' => $data['ten_nguyen_lieu'],
            ':dvt' => $data['don_vi_tinh'],
            ':nguong' => $data['nguong_canh_bao'],
            ':trang_thai' => $data['trang_thai'],
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO nguyen_lieu (ma_nguyen_lieu, ten_nguyen_lieu, don_vi_tinh, ton_kho_hien_tai, nguong_canh_bao, trang_thai)
             VALUES (:ma, :ten, :dvt, :ton_dau, :nguong, :trang_thai)"
        );
        $stmt->execute([
            ':ma' => $data['ma_nguyen_lieu'],
            ':ten' => $data['ten_nguyen_lieu'],
            ':dvt' => $data['don_vi_tinh'],
            ':ton_dau' => $data['ton_kho_hien_tai'] ?? 0,
            ':nguong' => $data['nguong_canh_bao'],
            ':trang_thai' => $data['trang_thai'],
        ]);
    }
}

function deleteIngredientById(int $id): bool
{
    $pdo = db_connect();
    $checks = [
        "SELECT COUNT(*) FROM cong_thuc_pha_che WHERE nguyen_lieu_id = :id",
        "SELECT COUNT(*) FROM chi_tiet_phieu_nhap WHERE nguyen_lieu_id = :id",
        "SELECT COUNT(*) FROM lich_su_kho WHERE nguyen_lieu_id = :id",
    ];
    foreach ($checks as $sqlCheck) {
        $st = $pdo->prepare($sqlCheck);
        $st->execute([':id' => $id]);
        if ((int) $st->fetchColumn() > 0) {
            return false;
        }
    }

    $stmt = $pdo->prepare("DELETE FROM nguyen_lieu WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return true;
}

/**
 * Tạo phiếu nhập kho, cộng tồn kho và ghi lịch sử kho trong 1 transaction.
 */
function createStockInReceipt(array $header, array $items): string
{
    $pdo = db_connect();
    $maPhieu = 'PN' . date('ymdHis') . random_int(10, 99);

    try {
        $pdo->beginTransaction();

        $totalAmount = 0.0;
        foreach ($items as $it) {
            $totalAmount += round(((float) $it['so_luong']) * ((float) $it['don_gia_nhap']), 2);
        }

        $stmtReceipt = $pdo->prepare(
            "INSERT INTO phieu_nhap_kho (ma_phieu_nhap, ten_nha_cung_cap, nguoi_tao_id, tong_tien, ghi_chu)
             VALUES (:ma, :ncc, :user_id, :tong_tien, :ghi_chu)"
        );
        $stmtReceipt->execute([
            ':ma' => $maPhieu,
            ':ncc' => $header['ten_nha_cung_cap'] ?: null,
            ':user_id' => $header['nguoi_tao_id'] ?: null,
            ':tong_tien' => $totalAmount,
            ':ghi_chu' => $header['ghi_chu'] ?: null,
        ]);
        $phieuId = (int) $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare(
            "INSERT INTO chi_tiet_phieu_nhap (phieu_nhap_id, nguyen_lieu_id, so_luong, don_gia_nhap, thanh_tien)
             VALUES (:phieu_id, :nl_id, :sl, :gia, :thanh_tien)"
        );
        $stmtLockNl = $pdo->prepare("SELECT ton_kho_hien_tai FROM nguyen_lieu WHERE id = :id FOR UPDATE");
        $stmtUpdateNl = $pdo->prepare("UPDATE nguyen_lieu SET ton_kho_hien_tai = :ton_moi WHERE id = :id");
        $stmtLog = $pdo->prepare(
            "INSERT INTO lich_su_kho (nguyen_lieu_id, loai_bien_dong, so_luong_thay_doi, ton_sau_bien_dong, ma_tham_chieu, ghi_chu)
             VALUES (:nl_id, 'nhap_kho', :delta, :ton_sau, :ma_ref, :ghi_chu)"
        );

        foreach ($items as $it) {
            $nlId = (int) $it['nguyen_lieu_id'];
            $qty = (float) $it['so_luong'];
            $unitPrice = (float) $it['don_gia_nhap'];
            $lineTotal = round($qty * $unitPrice, 2);

            $stmtDetail->execute([
                ':phieu_id' => $phieuId,
                ':nl_id' => $nlId,
                ':sl' => $qty,
                ':gia' => $unitPrice,
                ':thanh_tien' => $lineTotal,
            ]);

            $stmtLockNl->execute([':id' => $nlId]);
            $curStock = (float) ($stmtLockNl->fetchColumn() ?? 0);
            $newStock = round($curStock + $qty, 3);

            $stmtUpdateNl->execute([':ton_moi' => $newStock, ':id' => $nlId]);
            $stmtLog->execute([
                ':nl_id' => $nlId,
                ':delta' => $qty,
                ':ton_sau' => $newStock,
                ':ma_ref' => $maPhieu,
                ':ghi_chu' => $header['ghi_chu'] ?: 'Nhập kho nguyên liệu',
            ]);
        }

        $pdo->commit();
        return $maPhieu;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Xuất hủy / hao hụt nguyên liệu trực tiếp, trừ tồn kho và ghi lịch sử kho.
 */
function createManualStockOut(int $ingredientId, float $qty, string $lyDo): string
{
    $pdo = db_connect();
    $maThamChieu = 'XH' . date('ymdHis') . random_int(10, 99);

    try {
        $pdo->beginTransaction();

        $stmtLock = $pdo->prepare("SELECT ten_nguyen_lieu, don_vi_tinh, ton_kho_hien_tai FROM nguyen_lieu WHERE id = :id FOR UPDATE");
        $stmtLock->execute([':id' => $ingredientId]);
        $nl = $stmtLock->fetch();
        if (!$nl) {
            throw new RuntimeException('Nguyên liệu không tồn tại.');
        }

        $curStock = (float) $nl['ton_kho_hien_tai'];
        if ($qty <= 0 || $curStock < $qty) {
            throw new RuntimeException('Số lượng xuất vượt quá tồn kho hiện tại (' . $curStock . ' ' . $nl['don_vi_tinh'] . ').');
        }

        $newStock = round($curStock - $qty, 3);

        $stmtUpd = $pdo->prepare("UPDATE nguyen_lieu SET ton_kho_hien_tai = :ton WHERE id = :id");
        $stmtUpd->execute([':ton' => $newStock, ':id' => $ingredientId]);

        $stmtLog = $pdo->prepare(
            "INSERT INTO lich_su_kho (nguyen_lieu_id, loai_bien_dong, so_luong_thay_doi, ton_sau_bien_dong, ma_tham_chieu, ghi_chu)
             VALUES (:nl_id, 'xuat_huy', :delta, :ton_sau, :ma_ref, :ghi_chu)"
        );
        $stmtLog->execute([
            ':nl_id' => $ingredientId,
            ':delta' => -abs($qty),
            ':ton_sau' => $newStock,
            ':ma_ref' => $maThamChieu,
            ':ghi_chu' => $lyDo !== '' ? $lyDo : 'Xuất hủy / hao hụt kho',
        ]);

        $pdo->commit();
        return $maThamChieu;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function getRecipes(int $productId = 0): array
{
    $pdo = db_connect();
    $sql = "SELECT ct.id, ct.san_pham_id, ct.nguyen_lieu_id, ct.dinh_luong, ct.ghi_chu,
                   sp.ten_san_pham, nl.ten_nguyen_lieu, nl.don_vi_tinh, nl.ton_kho_hien_tai
            FROM cong_thuc_pha_che ct
            INNER JOIN san_pham sp ON sp.id = ct.san_pham_id
            INNER JOIN nguyen_lieu nl ON nl.id = ct.nguyen_lieu_id";
    $params = [];
    if ($productId > 0) {
        $sql .= " WHERE ct.san_pham_id = :pid";
        $params[':pid'] = $productId;
    }
    $sql .= " ORDER BY sp.ten_san_pham ASC, nl.ten_nguyen_lieu ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function saveRecipeItem(int $productId, int $ingredientId, float $dinhLuong, string $ghiChu = ''): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO cong_thuc_pha_che (san_pham_id, nguyen_lieu_id, dinh_luong, ghi_chu)
         VALUES (:pid, :nl_id, :dinh_luong, :ghi_chu)
         ON DUPLICATE KEY UPDATE dinh_luong = VALUES(dinh_luong), ghi_chu = VALUES(ghi_chu)"
    );
    $stmt->execute([
        ':pid' => $productId,
        ':nl_id' => $ingredientId,
        ':dinh_luong' => $dinhLuong,
        ':ghi_chu' => $ghiChu !== '' ? $ghiChu : null,
    ]);
}

function deleteRecipeItem(int $recipeId): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM cong_thuc_pha_che WHERE id = :id");
    $stmt->execute([':id' => $recipeId]);
}

function getInventoryMovements(string $movementType = 'all', int $limit = 50): array
{
    $pdo = db_connect();
    $sql = "SELECT ls.id, ls.nguyen_lieu_id, ls.loai_bien_dong, ls.so_luong_thay_doi,
                   ls.ton_sau_bien_dong, ls.ma_tham_chieu, ls.ghi_chu, ls.ngay_tao,
                   nl.ten_nguyen_lieu, nl.ma_nguyen_lieu, nl.don_vi_tinh
            FROM lich_su_kho ls
            INNER JOIN nguyen_lieu nl ON nl.id = ls.nguyen_lieu_id
            WHERE 1=1";
    $params = [];

    $allowedTypes = ['nhap_kho', 'xuat_ban', 'hoan_don', 'xuat_huy'];
    if ($movementType !== 'all' && in_array($movementType, $allowedTypes, true)) {
        $sql .= " AND ls.loai_bien_dong = :loai";
        $params[':loai'] = $movementType;
    }

    $sql .= " ORDER BY ls.ngay_tao DESC, ls.id DESC LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function getRecentStockInReceipts(int $limit = 15): array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT pn.id, pn.ma_phieu_nhap, pn.ten_nha_cung_cap, pn.tong_tien, pn.ghi_chu, pn.ngay_nhap,
                nd.ho_ten AS nguoi_tao
         FROM phieu_nhap_kho pn
         LEFT JOIN nguoi_dung nd ON nd.id = pn.nguoi_tao_id
         ORDER BY pn.ngay_nhap DESC, pn.id DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getInventorySummaryStats(): array
{
    $pdo = db_connect();
    $counts = $pdo->query(
        "SELECT
            COUNT(*) AS total_ingredients,
            SUM(CASE WHEN ton_kho_hien_tai <= 0 THEN 1 ELSE 0 END) AS out_of_stock_count,
            SUM(CASE WHEN ton_kho_hien_tai > 0 AND ton_kho_hien_tai <= nguong_canh_bao THEN 1 ELSE 0 END) AS low_stock_count
         FROM nguyen_lieu"
    )->fetch() ?: [];

    $movements = $pdo->query(
        "SELECT
            COALESCE(SUM(CASE WHEN loai_bien_dong = 'nhap_kho' THEN so_luong_thay_doi ELSE 0 END), 0) AS total_imported_qty,
            COALESCE(SUM(CASE WHEN loai_bien_dong IN ('xuat_ban', 'xuat_huy') THEN ABS(so_luong_thay_doi) ELSE 0 END), 0) AS total_exported_qty
         FROM lich_su_kho"
    )->fetch() ?: [];

    return array_merge($counts, $movements);
}
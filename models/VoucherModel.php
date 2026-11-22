<?php

/**
 * Tìm mã giảm giá đang bật theo mã code (không phân biệt hoa thường).
 */
function findActiveVoucherByCode(string $code): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ma_code, kieu_giam, gia_tri_giam, don_toi_thieu, ngay_het_han, con_hieu_luc
         FROM ma_giam_gia
         WHERE UPPER(ma_code) = :code AND con_hieu_luc = 1
         LIMIT 1"
    );
    $stmt->execute([':code' => strtoupper(trim($code))]);
    $voucher = $stmt->fetch();

    return $voucher ?: null;
}
<?php

/**
 * Lưu đơn hàng và danh sách món chi tiết trong một transaction để đảm bảo toàn vẹn dữ liệu.
 */
function createOrderWithItems(array $orderData, array $cartItems): string
{
    $pdo = db_connect();
    $maDonHang = 'DH' . date('ymdHis') . random_int(10, 99);

    try {
        $pdo->beginTransaction();

        $stmtOrder = $pdo->prepare(
            "INSERT INTO don_hang (
                ma_don_hang, nguoi_dung_id, ma_giam_gia_id,
                ten_nguoi_nhan, sdt_nguoi_nhan, hinh_thuc_nhan, dia_chi_giao,
                phuong_thuc_thanh_toan, tam_tinh, tien_giam, tong_thanh_toan,
                trang_thai_don, da_tru_kho, ghi_chu
            ) VALUES (
                :ma_don_hang, :nguoi_dung_id, :ma_giam_gia_id,
                :ten_nguoi_nhan, :sdt_nguoi_nhan, :hinh_thuc_nhan, :dia_chi_giao,
                :phuong_thuc_thanh_toan, :tam_tinh, :tien_giam, :tong_thanh_toan,
                'cho_xac_nhan', 0, :ghi_chu
            )"
        );
        $stmtOrder->execute([
            ':ma_don_hang' => $maDonHang,
            ':nguoi_dung_id' => $orderData['nguoi_dung_id'],
            ':ma_giam_gia_id' => $orderData['ma_giam_gia_id'],
            ':ten_nguoi_nhan' => $orderData['ten_nguoi_nhan'],
            ':sdt_nguoi_nhan' => $orderData['sdt_nguoi_nhan'],
            ':hinh_thuc_nhan' => $orderData['hinh_thuc_nhan'],
            ':dia_chi_giao' => $orderData['dia_chi_giao'],
            ':phuong_thuc_thanh_toan' => $orderData['phuong_thuc_thanh_toan'],
            ':tam_tinh' => $orderData['tam_tinh'],
            ':tien_giam' => $orderData['tien_giam'],
            ':tong_thanh_toan' => $orderData['tong_thanh_toan'],
            ':ghi_chu' => $orderData['ghi_chu'],
        ]);

        $donHangId = (int) $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare(
            "INSERT INTO chi_tiet_don_hang (
                don_hang_id, san_pham_id, bien_the_id,
                ten_san_pham, ten_size, don_gia, so_luong, thanh_tien
            ) VALUES (
                :don_hang_id, :san_pham_id, :bien_the_id,
                :ten_san_pham, :ten_size, :don_gia, :so_luong, :thanh_tien
            )"
        );

        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $sizeId = (int) ($item['size_id'] ?? 0);
            $unitPrice = (float) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $lineTotal = round($unitPrice * $qty, 2);

            $rawName = (string) ($item['name'] ?? 'Món');
            $tenSize = null;
            if (preg_match('/\(Size\s+([^)]+)\)/u', $rawName, $mSize)) {
                $tenSize = mb_substr(trim($mSize[1]), 0, 30);
            }

            // Ghi chú gắn kèm tên món để không làm thay đổi schema bảng chi_tiet_don_hang.
            $itemNote = trim((string) ($item['note'] ?? ''));
            $fullItemName = $itemNote !== '' ? ($rawName . ' [Ghi chú: ' . $itemNote . ']') : $rawName;
            $fullItemName = mb_substr($fullItemName, 0, 150);

            $stmtDetail->execute([
                ':don_hang_id' => $donHangId,
                ':san_pham_id' => $productId,
                ':bien_the_id' => $sizeId > 0 ? $sizeId : null,
                ':ten_san_pham' => $fullItemName,
                ':ten_size' => $tenSize,
                ':don_gia' => $unitPrice,
                ':so_luong' => $qty,
                ':thanh_tien' => $lineTotal,
            ]);
        }

        $pdo->commit();
        return $maDonHang;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Lấy danh sách đơn hàng kèm chi tiết món theo điều kiện lọc (dùng cho khách hoặc admin).
 */
function getOrdersWithItems(?int $userId = null, string $statusFilter = 'all'): array
{
    $pdo = db_connect();
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];

    $sql = "SELECT id, ma_don_hang, nguoi_dung_id, ten_nguoi_nhan, sdt_nguoi_nhan,
                   hinh_thuc_nhan, dia_chi_giao, phuong_thuc_thanh_toan,
                   tam_tinh, tien_giam, tong_thanh_toan, trang_thai_don,
                   ghi_chu, ngay_tao
            FROM don_hang";
    $where = [];
    $params = [];

    if ($userId !== null && $userId > 0) {
        $where[] = "nguoi_dung_id = :user_id";
        $params[':user_id'] = $userId;
    }

    if ($statusFilter !== 'all' && in_array($statusFilter, $allowedStatuses, true)) {
        $where[] = "trang_thai_don = :status";
        $params[':status'] = $statusFilter;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY ngay_tao DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    $itemsByOrder = [];
    if (!empty($orders)) {
        $orderIds = array_map(static fn($row) => (int) $row['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmtItems = $pdo->prepare(
            "SELECT don_hang_id, ten_san_pham, ten_size, don_gia, so_luong, thanh_tien
             FROM chi_tiet_don_hang
             WHERE don_hang_id IN ($placeholders)
             ORDER BY id ASC"
        );
        $stmtItems->execute($orderIds);
        foreach ($stmtItems->fetchAll() as $item) {
            $itemsByOrder[(int) $item['don_hang_id']][] = $item;
        }
    }

    return [
        'orders' => $orders,
        'itemsByOrder' => $itemsByOrder,
    ];
}

/**
 * Cập nhật trạng thái xử lý của một đơn hàng.
 */
function updateOrderStatus(int $orderId, string $newStatus): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE don_hang SET trang_thai_don = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $orderId,
    ]);
}
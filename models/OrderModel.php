<?php
/**
 * Lưu đơn hàng và danh sách món chi tiết trong một transaction để đảm bảo toàn vẹn dữ liệu.
 */
require_once __DIR__ . '/ProductModel.php';

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
                ten_san_pham, ten_size, ghi_chu, don_gia, so_luong, thanh_tien
            ) VALUES (
                :don_hang_id, :san_pham_id, NULL,
                :ten_san_pham, NULL, :ghi_chu, :don_gia, :so_luong, :thanh_tien
            )"
        );

        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $unitPrice = (float) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $lineTotal = round($unitPrice * $qty, 2);
            $rawName = (string) ($item['name'] ?? 'Món');
            $itemNote = trim((string) ($item['note'] ?? ''));
            $fullItemName = $itemNote !== '' ? ($rawName . ' [Ghi chú: ' . $itemNote . ']') : $rawName;
            $fullItemName = mb_substr($fullItemName, 0, 150);

            $stmtDetail->execute([
                ':don_hang_id' => $donHangId,
                ':san_pham_id' => $productId,
                ':ten_san_pham' => $fullItemName,
                ':ghi_chu' => $itemNote !== '' ? $itemNote : null,
                ':don_gia' => $unitPrice,
                ':so_luong' => $qty,
                ':thanh_tien' => $lineTotal,
            ]);

            $chiTietDonHangId = (int) $pdo->lastInsertId();
            $selectedToppingIds = array_values(array_unique(array_map('intval', (array) ($item['topping_ids'] ?? []))));
            if (!empty($selectedToppingIds)) {
                $toppingRows = getActiveToppingsByIds($selectedToppingIds);
                $stmtTopping = $pdo->prepare(
                    "INSERT INTO topping_don_hang (
                        chi_tiet_don_hang_id, topping_id, ten_topping, gia_topping, so_luong
                    ) VALUES (
                        :chi_tiet_don_hang_id, :topping_id, :ten_topping, :gia_topping, :so_luong
                    )"
                );
                foreach ($toppingRows as $tp) {
                    $stmtTopping->execute([
                        ':chi_tiet_don_hang_id' => $chiTietDonHangId,
                        ':topping_id' => (int) $tp['id'],
                        ':ten_topping' => (string) $tp['ten_topping'],
                        ':gia_topping' => (float) $tp['gia_ban'],
                        ':so_luong' => $qty,
                    ]);
                }
            }
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
 * Cập nhật trạng thái xử lý của một đơn hàng:
 * - Tự động trừ kho khi chuyển sang dang_pha_che / dang_giao / hoan_thanh (nếu da_tru_kho = 0).
 * - Tự động hoàn kho khi chuyển sang da_huy (nếu da_tru_kho = 1).
 */
function updateOrderStatus(int $orderId, string $newStatus): void
{
    $pdo = db_connect();
    $deductStatuses = ['dang_pha_che', 'dang_giao', 'hoan_thanh'];

    try {
        $pdo->beginTransaction();

        $stmtOrder = $pdo->prepare(
            "SELECT id, ma_don_hang, trang_thai_don, da_tru_kho
             FROM don_hang
             WHERE id = :id
             FOR UPDATE"
        );
        $stmtOrder->execute([':id' => $orderId]);
        $order = $stmtOrder->fetch();

        if (!$order) {
            throw new RuntimeException('Không tìm thấy đơn hàng.');
        }

        $daTruKho = (int) ($order['da_tru_kho'] ?? 0);
        $maDonHang = (string) ($order['ma_don_hang'] ?? ('DH#' . $orderId));

        if (in_array($newStatus, $deductStatuses, true) && $daTruKho === 0) {
            $stmtItems = $pdo->prepare(
                "SELECT san_pham_id, so_luong
                 FROM chi_tiet_don_hang
                 WHERE don_hang_id = :order_id"
            );
            $stmtItems->execute([':order_id' => $orderId]);
            $orderItems = $stmtItems->fetchAll();

            $neededByIngredient = [];
            foreach ($orderItems as $item) {
                $productId = (int) ($item['san_pham_id'] ?? 0);
                $qtySold = max(1, (int) ($item['so_luong'] ?? 1));
                if ($productId <= 0) {
                    continue;
                }

                $stmtRecipe = $pdo->prepare(
                    "SELECT nguyen_lieu_id, dinh_luong
                     FROM cong_thuc_pha_che
                     WHERE san_pham_id = :product_id"
                );
                $stmtRecipe->execute([':product_id' => $productId]);
                foreach ($stmtRecipe->fetchAll() as $rc) {
                    $nlId = (int) $rc['nguyen_lieu_id'];
                    $need = round(((float) $rc['dinh_luong']) * $qtySold, 3);
                    $neededByIngredient[$nlId] = round(($neededByIngredient[$nlId] ?? 0.0) + $need, 3);
                }
            }

            foreach ($neededByIngredient as $nlId => $needQty) {
                $stmtLockNl = $pdo->prepare(
                    "SELECT id, ten_nguyen_lieu, don_vi_tinh, ton_kho_hien_tai
                     FROM nguyen_lieu
                     WHERE id = :id
                     FOR UPDATE"
                );
                $stmtLockNl->execute([':id' => $nlId]);
                $nl = $stmtLockNl->fetch();
                if (!$nl) {
                    continue;
                }

                $curStock = (float) $nl['ton_kho_hien_tai'];
                if ($curStock < $needQty) {
                    throw new RuntimeException(
                        'Không đủ tồn kho "' . $nl['ten_nguyen_lieu'] . '" (cần ' . $needQty . ' ' . $nl['don_vi_tinh'] . ', còn ' . $curStock . ' ' . $nl['don_vi_tinh'] . ').'
                    );
                }

                $newStock = round($curStock - $needQty, 3);
                $stmtDeductNl = $pdo->prepare(
                    "UPDATE nguyen_lieu
                     SET ton_kho_hien_tai = :ton_moi
                     WHERE id = :id"
                );
                $stmtDeductNl->execute([
                    ':ton_moi' => $newStock,
                    ':id' => $nlId,
                ]);

                $stmtLog = $pdo->prepare(
                    "INSERT INTO lich_su_kho (
                        nguyen_lieu_id, loai_bien_dong, so_luong_thay_doi,
                        ton_sau_bien_dong, ma_tham_chieu, ghi_chu
                    ) VALUES (
                        :nl_id, 'xuat_ban', :delta, :ton_sau, :ma_ref, :ghi_chu
                    )"
                );
                $stmtLog->execute([
                    ':nl_id' => $nlId,
                    ':delta' => -abs($needQty),
                    ':ton_sau' => $newStock,
                    ':ma_ref' => $maDonHang,
                    ':ghi_chu' => 'Trừ kho tự động khi xử lý đơn ' . $maDonHang,
                ]);
            }

            $stmtUpd = $pdo->prepare(
                "UPDATE don_hang
                 SET trang_thai_don = :status, da_tru_kho = 1
                 WHERE id = :id"
            );
            $stmtUpd->execute([
                ':status' => $newStatus,
                ':id' => $orderId,
            ]);
        } elseif ($newStatus === 'da_huy' && $daTruKho === 1) {
            $stmtItems = $pdo->prepare(
                "SELECT san_pham_id, so_luong
                 FROM chi_tiet_don_hang
                 WHERE don_hang_id = :order_id"
            );
            $stmtItems->execute([':order_id' => $orderId]);
            $orderItems = $stmtItems->fetchAll();

            $refundByIngredient = [];
            foreach ($orderItems as $item) {
                $productId = (int) ($item['san_pham_id'] ?? 0);
                $qtySold = max(1, (int) ($item['so_luong'] ?? 1));
                if ($productId <= 0) {
                    continue;
                }

                $stmtRecipe = $pdo->prepare(
                    "SELECT nguyen_lieu_id, dinh_luong
                     FROM cong_thuc_pha_che
                     WHERE san_pham_id = :product_id"
                );
                $stmtRecipe->execute([':product_id' => $productId]);
                foreach ($stmtRecipe->fetchAll() as $rc) {
                    $nlId = (int) $rc['nguyen_lieu_id'];
                    $refQty = round(((float) $rc['dinh_luong']) * $qtySold, 3);
                    $refundByIngredient[$nlId] = round(($refundByIngredient[$nlId] ?? 0.0) + $refQty, 3);
                }
            }

            foreach ($refundByIngredient as $nlId => $refundQty) {
                $stmtLockNl = $pdo->prepare(
                    "SELECT id, ton_kho_hien_tai
                     FROM nguyen_lieu
                     WHERE id = :id
                     FOR UPDATE"
                );
                $stmtLockNl->execute([':id' => $nlId]);
                $nl = $stmtLockNl->fetch();
                if (!$nl) {
                    continue;
                }

                $curStock = (float) $nl['ton_kho_hien_tai'];
                $newStock = round($curStock + $refundQty, 3);

                $stmtRefundNl = $pdo->prepare(
                    "UPDATE nguyen_lieu
                     SET ton_kho_hien_tai = :ton_moi
                     WHERE id = :id"
                );
                $stmtRefundNl->execute([
                    ':ton_moi' => $newStock,
                    ':id' => $nlId,
                ]);

                $stmtLog = $pdo->prepare(
                    "INSERT INTO lich_su_kho (
                        nguyen_lieu_id, loai_bien_dong, so_luong_thay_doi,
                        ton_sau_bien_dong, ma_tham_chieu, ghi_chu
                    ) VALUES (
                        :nl_id, 'hoan_don', :delta, :ton_sau, :ma_ref, :ghi_chu
                    )"
                );
                $stmtLog->execute([
                    ':nl_id' => $nlId,
                    ':delta' => abs($refundQty),
                    ':ton_sau' => $newStock,
                    ':ma_ref' => $maDonHang,
                    ':ghi_chu' => 'Hoàn kho tự động khi hủy đơn ' . $maDonHang,
                ]);
            }

            $stmtUpd = $pdo->prepare(
                "UPDATE don_hang
                 SET trang_thai_don = 'da_huy', da_tru_kho = 0
                 WHERE id = :id"
            );
            $stmtUpd->execute([':id' => $orderId]);
        } else {
            $stmtUpd = $pdo->prepare(
                "UPDATE don_hang
                 SET trang_thai_don = :status
                 WHERE id = :id"
            );
            $stmtUpd->execute([
                ':status' => $newStatus,
                ':id' => $orderId,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

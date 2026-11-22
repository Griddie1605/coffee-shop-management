<?php

require_once __DIR__ . '/../models/ProductModel.php';

function orderIndex(): void
{
    if (empty($_SESSION['user']['id'])) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login&redirect=orders');
        exit;
    }

    $userId = (int) $_SESSION['user']['id'];
    $pdo = db_connect();

    // Lấy toàn bộ đơn hàng của tài khoản đang đăng nhập
    $stmtOrders = $pdo->prepare(
        "SELECT id, ma_don_hang, ten_nguoi_nhan, sdt_nguoi_nhan,
                hinh_thuc_nhan, dia_chi_giao, phuong_thuc_thanh_toan,
                tam_tinh, tien_giam, tong_thanh_toan, trang_thai_don,
                ghi_chu, ngay_tao
         FROM don_hang
         WHERE nguoi_dung_id = :user_id
         ORDER BY ngay_tao DESC, id DESC"
    );
    $stmtOrders->execute([':user_id' => $userId]);
    $orders = $stmtOrders->fetchAll();

    // Lấy chi tiết món cho tất cả đơn hàng trên (tránh query N+1)
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
            $oid = (int) $item['don_hang_id'];
            $itemsByOrder[$oid][] = $item;
        }
    }

    render('order/index', [
        'pageTitle' => 'Đơn hàng của tôi - 47th cafe',
        'orders' => $orders,
        'itemsByOrder' => $itemsByOrder,
    ]);
}
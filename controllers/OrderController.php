<?php

require_once __DIR__ . '/../models/OrderModel.php';

/**
 * Hiển thị lịch sử đơn hàng và trạng thái xử lý của tài khoản đang đăng nhập.
 */
function orderIndex(): void
{
    if (empty($_SESSION['user']['id'])) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login&redirect=orders');
        exit;
    }

    $userId = (int) $_SESSION['user']['id'];
    $data = getOrdersWithItems($userId);

    render('order/index', [
        'pageTitle' => 'Đơn hàng của tôi - 47th cafe',
        'orders' => $data['orders'],
        'itemsByOrder' => $data['itemsByOrder'],
    ]);
}
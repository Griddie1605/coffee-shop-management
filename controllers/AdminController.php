<?php

/**
 * Entry point duy nhất của các route admin.
 * Giữ tên AdminController và các hàm action cũ để không thay đổi URL hiện tại.
 */
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/admin/orders.php';
require_once __DIR__ . '/admin/catalog.php';
require_once __DIR__ . '/admin/dashboard.php';
require_once __DIR__ . '/admin/customers.php';
require_once __DIR__ . '/admin/options.php';

/**
 * Kiểm tra quyền nhân viên/quản trị trước khi bất kỳ action admin nào xử lý request.
 */
function adminRequireStaff(): array
{
    $user = $_SESSION['user'] ?? null;
    if (empty($user) || !in_array((string) ($user['vai_tro'] ?? ''), ['quan_tri', 'nhan_vien'], true)) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login');
        exit;
    }

    return $user;
}

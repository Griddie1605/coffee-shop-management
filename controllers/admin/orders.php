<?php

require_once __DIR__ . '/../../models/OrderModel.php';

/**
 * Danh sách đơn hàng phía quản trị có lọc theo trạng thái.
 */
function adminIndex(): void
{
    adminRequireStaff();

    $statusFilter = (string) ($_GET['status'] ?? 'all');
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];
    if (!in_array($statusFilter, $allowedStatuses, true)) {
        $statusFilter = 'all';
    }

    $data = getOrdersWithItems(null, $statusFilter);

    render('admin/index', [
        'pageTitle' => 'Quản trị đơn hàng - 47th cafe',
        'orders' => $data['orders'],
        'itemsByOrder' => $data['itemsByOrder'],
        'statusFilter' => $statusFilter,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

/**
 * Cập nhật trạng thái xử lý của đơn hàng.
 */
function adminUpdateStatus(): void
{
    adminRequireStaff();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=index');
        exit;
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = (string) ($_POST['trang_thai_don'] ?? '');
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];

    if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
        $_SESSION['admin_error'] = 'Dữ liệu cập nhật trạng thái đơn không hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=index');
        exit;
    }

    updateOrderStatus($orderId, $newStatus);
    $_SESSION['admin_success'] = 'Đã cập nhật trạng thái đơn hàng.';

    $redirectStatus = (string) ($_POST['return_status'] ?? 'all');
    header('Location: ' . BASE_URL . '/?c=admin&a=index' . ($redirectStatus !== 'all' ? '&status=' . urlencode($redirectStatus) : ''));
    exit;
}
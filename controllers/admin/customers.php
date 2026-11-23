<?php

require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/OrderModel.php';

/**
 * Danh sách khách hàng, tìm kiếm và xem chi tiết lịch sử đơn của khách được chọn.
 */
function adminCustomers(): void
{
    adminRequireStaff();

    $keyword = trim((string) ($_GET['q'] ?? ''));
    $selectedCustomerId = (int) ($_GET['customer_id'] ?? 0);

    $customers = getCustomersWithStats($keyword);
    $selectedCustomer = null;
    $customerOrders = [];
    $orderItemsByOrderId = [];

    if ($selectedCustomerId > 0) {
        foreach ($customers as $c) {
            if ((int) $c['id'] === $selectedCustomerId) {
                $selectedCustomer = $c;
                break;
            }
        }
        if ($selectedCustomer === null) {
            $selectedCustomer = findCustomerById($selectedCustomerId);
        }
        if ($selectedCustomer !== null) {
            $orderData = getOrdersWithItems((int) $selectedCustomer['id']);
            $customerOrders = $orderData['orders'];
            $orderItemsByOrderId = $orderData['itemsByOrder'];
        }
    }

    render('admin/customers', [
        'pageTitle' => 'Quản lý khách hàng - CF Song Thư',
        'customers' => $customers,
        'keyword' => $keyword,
        'selectedCustomer' => $selectedCustomer,
        'customerOrders' => $customerOrders,
        'orderItemsByOrderId' => $orderItemsByOrderId,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

/**
 * Khóa hoặc mở tài khoản khách hàng.
 */
function adminToggleCustomerStatus(): void
{
    adminRequireStaff();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=customers');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        toggleCustomerActiveStatus($id);
        $_SESSION['admin_success'] = 'Đã cập nhật trạng thái tài khoản khách hàng.';
    }

    $query = ['c' => 'admin', 'a' => 'customers'];
    $q = trim((string) ($_POST['q'] ?? ''));
    $selectedId = (int) ($_POST['customer_id'] ?? 0);
    if ($q !== '') {
        $query['q'] = $q;
    }
    if ($selectedId > 0) {
        $query['customer_id'] = $selectedId;
    }

    header('Location: ' . BASE_URL . '/?' . http_build_query($query));
    exit;
}
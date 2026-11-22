<?php

require_once __DIR__ . '/../models/ProductModel.php';

function adminRequireStaff(): array
{
    $user = $_SESSION['user'] ?? null;
    if (empty($user) || !in_array((string) ($user['vai_tro'] ?? ''), ['quan_tri', 'nhan_vien'], true)) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login');
        exit;
    }
    return $user;
}

function adminIndex(): void
{
    adminRequireStaff();

    $statusFilter = (string) ($_GET['status'] ?? 'all');
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];

    $pdo = db_connect();
    $sql = "SELECT id, ma_don_hang, nguoi_dung_id, ten_nguoi_nhan, sdt_nguoi_nhan,
                   hinh_thuc_nhan, dia_chi_giao, phuong_thuc_thanh_toan,
                   tam_tinh, tien_giam, tong_thanh_toan, trang_thai_don,
                   ghi_chu, ngay_tao
            FROM don_hang";
    $params = [];

    if ($statusFilter !== 'all' && in_array($statusFilter, $allowedStatuses, true)) {
        $sql .= " WHERE trang_thai_don = :status";
        $params[':status'] = $statusFilter;
    } else {
        $statusFilter = 'all';
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

    render('admin/index', [
        'pageTitle' => 'Quản trị đơn hàng - 47th cafe',
        'orders' => $orders,
        'itemsByOrder' => $itemsByOrder,
        'statusFilter' => $statusFilter,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

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

    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE don_hang SET trang_thai_don = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $orderId,
    ]);

    $_SESSION['admin_success'] = 'Đã cập nhật trạng thái đơn hàng.';
    $redirectStatus = (string) ($_POST['return_status'] ?? 'all');
    header('Location: ' . BASE_URL . '/?c=admin&a=index' . ($redirectStatus !== 'all' ? '&status=' . urlencode($redirectStatus) : ''));
    exit;
}
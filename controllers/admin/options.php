<?php

require_once __DIR__ . '/../../models/ProductModel.php';

/**
 * Thêm hoặc sửa size của một sản phẩm.
 */
function adminSaveSize(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $productId = (int) ($_POST['san_pham_id'] ?? 0);
    $tenSize = trim((string) ($_POST['ten_size'] ?? ''));
    $giaCongThem = (float) ($_POST['gia_cong_them'] ?? 0);
    $laMacDinh = isset($_POST['la_mac_dinh']) ? 1 : 0;
    $conBan = isset($_POST['con_ban']) ? 1 : 0;

    if ($productId <= 0 || $tenSize === '' || $giaCongThem < 0) {
        $_SESSION['admin_error'] = 'Vui lòng chọn món, nhập tên size và giá cộng thêm hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
        exit;
    }

    if (findProductById($productId) === null) {
        $_SESSION['admin_error'] = 'Sản phẩm được chọn không tồn tại.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
        exit;
    }

    saveSizeRecord([
        'san_pham_id' => $productId,
        'ten_size' => $tenSize,
        'gia_cong_them' => $giaCongThem,
        'la_mac_dinh' => $laMacDinh,
        'con_ban' => $conBan,
    ], $id);

    $_SESSION['admin_success'] = $id > 0 ? 'Đã cập nhật size sản phẩm.' : 'Đã thêm size mới cho sản phẩm.';
    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

/**
 * Bật/tắt áp dụng của size.
 */
function adminToggleSize(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        toggleSizeActive($id);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của size.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

/**
 * Thêm hoặc sửa topping.
 */
function adminSaveTopping(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $tenTopping = trim((string) ($_POST['ten_topping'] ?? ''));
    $giaBan = (float) ($_POST['gia_ban'] ?? 0);
    $conBan = isset($_POST['con_ban']) ? 1 : 0;

    if ($tenTopping === '' || $giaBan < 0) {
        $_SESSION['admin_error'] = 'Vui lòng nhập tên topping và giá bán hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
        exit;
    }

    saveToppingRecord([
        'ten_topping' => $tenTopping,
        'gia_ban' => $giaBan,
        'con_ban' => $conBan,
    ], $id);

    $_SESSION['admin_success'] = $id > 0 ? 'Đã cập nhật topping.' : 'Đã thêm topping mới.';
    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

/**
 * Bật/tắt trạng thái bán của topping.
 */
function adminToggleTopping(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        toggleToppingActive($id);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của topping.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}
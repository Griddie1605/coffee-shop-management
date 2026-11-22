<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/VoucherModel.php';

/**
 * Tính lại thông tin món, size, topping và đơn giá từ CSDL để chống chỉnh sửa giá phía client.
 */
function buildValidatedCartItem(int $productId, int $sizeId, array $rawToppingIds, string $note, int $qty): ?array
{
    $product = findActiveProductById($productId);
    if (!$product) {
        return null;
    }

    $unitPrice = (float) $product['gia_goc'];
    $sizeName = '';

    if ($sizeId > 0) {
        $sizeRow = findActiveProductSize($sizeId, $productId);
        if ($sizeRow) {
            $sizeName = (string) $sizeRow['ten_size'];
            $unitPrice += (float) $sizeRow['gia_cong_them'];
        } else {
            $sizeId = 0;
        }
    }

    $toppingRows = getActiveToppingsByIds($rawToppingIds);
    $toppingIds = [];
    $toppingNames = [];
    foreach ($toppingRows as $top) {
        $toppingIds[] = (int) $top['id'];
        $toppingNames[] = (string) $top['ten_topping'];
        $unitPrice += (float) $top['gia_ban'];
    }

    // Tách dòng giỏ hàng khi món, size, topping hoặc ghi chú khác nhau.
    $noteHash = $note !== '' ? md5($note) : '0';
    $key = 'p_' . $productId
        . '_s_' . $sizeId
        . '_t_' . (!empty($toppingIds) ? implode('-', $toppingIds) : '0')
        . '_n_' . $noteHash;

    $displayName = (string) $product['ten_san_pham'];
    if ($sizeName !== '') {
        $displayName .= ' (Size ' . $sizeName . ')';
    }
    if (!empty($toppingNames)) {
        $displayName .= ' + ' . implode(', ', $toppingNames);
    }

    return [
        'key' => $key,
        'product_name' => (string) $product['ten_san_pham'],
        'item' => [
            'product_id' => $productId,
            'size_id' => $sizeId,
            'topping_ids' => $toppingIds,
            'name' => $displayName,
            'note' => $note,
            'price' => $unitPrice,
            'qty' => max(1, min(50, $qty)),
        ],
    ];
}

/**
 * Thêm món vào giỏ hàng.
 */
function cartAdd(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $sizeId = (int) ($_POST['size_id'] ?? 0);
    $rawToppingIds = is_array($_POST['topping_ids'] ?? null) ? $_POST['topping_ids'] : [];
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 200);

    $built = buildValidatedCartItem($productId, $sizeId, $rawToppingIds, $note, $qty);
    if ($built === null) {
        $_SESSION['flash_message'] = 'Món không tồn tại hoặc đã ngừng bán.';
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $key = $built['key'];
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = min(50, (int) $_SESSION['cart'][$key]['qty'] + $qty);
    } else {
        $_SESSION['cart'][$key] = $built['item'];
    }

    $_SESSION['flash_message'] = 'Đã thêm "' . $built['product_name'] . '" (x' . $qty . ') vào giỏ hàng.';
    header('Location: ' . BASE_URL . '/?c=home&a=menu');
    exit;
}

/**
 * Hiển thị giỏ hàng kèm danh sách size/topping để chỉnh sửa trực tiếp.
 */
function cartIndex(): void
{
    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $totalAmount = 0.0;
    $sizesByProduct = [];

    foreach ($cartItems as $item) {
        $totalAmount += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
        $pid = (int) ($item['product_id'] ?? 0);
        if ($pid > 0 && !isset($sizesByProduct[$pid])) {
            $sizesByProduct[$pid] = getProductSizes($pid);
        }
    }

    $toppings = getActiveToppings();
    $discountAmount = 0.0;
    $appliedVoucher = isset($_SESSION['voucher']) && is_array($_SESSION['voucher']) ? $_SESSION['voucher'] : null;

    if ($appliedVoucher !== null && $totalAmount > 0) {
        $expired = !empty($appliedVoucher['ngay_het_han']) && strtotime((string) $appliedVoucher['ngay_het_han']) < time();
        if ($expired || $totalAmount < (float) ($appliedVoucher['don_toi_thieu'] ?? 0)) {
            unset($_SESSION['voucher']);
            $appliedVoucher = null;
        } else {
            $discountAmount = calculateVoucherDiscount($appliedVoucher, $totalAmount);
        }
    }

    $finalAmount = max(0.0, $totalAmount - $discountAmount);

    render('cart/index', [
        'pageTitle' => 'Giỏ hàng - 47th cafe',
        'cartItems' => $cartItems,
        'sizesByProduct' => $sizesByProduct,
        'toppings' => $toppings,
        'totalAmount' => $totalAmount,
        'appliedVoucher' => $appliedVoucher,
        'discountAmount' => $discountAmount,
        'finalAmount' => $finalAmount,
    ]);
}

/**
 * Cập nhật số lượng các dòng trong giỏ (số lượng <= 0 sẽ xóa dòng đó).
 */
function cartUpdate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $quantities = $_POST['qty'] ?? [];
    if (is_array($quantities) && isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($quantities as $key => $qty) {
            if (!isset($_SESSION['cart'][$key])) {
                continue;
            }
            $newQty = (int) $qty;
            if ($newQty <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['qty'] = min(50, $newQty);
            }
        }
    }

    $_SESSION['cart_message'] = 'Đã cập nhật giỏ hàng.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}

/**
 * Xóa một dòng khỏi giỏ hàng.
 */
function cartRemove(): void
{
    $key = (string) ($_GET['key'] ?? '');
    if ($key !== '' && isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart_message'] = 'Đã xóa món khỏi giỏ hàng.';
    }

    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}

/**
 * Sửa lại size, topping, ghi chú hoặc số lượng của một dòng đang có trong giỏ.
 */
function cartEdit(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $oldKey = (string) ($_POST['old_key'] ?? '');
    if ($oldKey === '' || !isset($_SESSION['cart'][$oldKey])) {
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $productId = (int) ($_SESSION['cart'][$oldKey]['product_id'] ?? 0);
    $sizeId = (int) ($_POST['size_id'] ?? 0);
    $rawToppingIds = is_array($_POST['topping_ids'] ?? null) ? $_POST['topping_ids'] : [];
    $qty = max(1, min(50, (int) ($_POST['qty'] ?? 1)));
    $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 200);

    $built = buildValidatedCartItem($productId, $sizeId, $rawToppingIds, $note, $qty);
    unset($_SESSION['cart'][$oldKey]);

    if ($built === null) {
        $_SESSION['cart_message'] = 'Món không còn bán nên đã xóa khỏi giỏ.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $newKey = $built['key'];
    if (isset($_SESSION['cart'][$newKey])) {
        $_SESSION['cart'][$newKey]['qty'] = min(50, (int) $_SESSION['cart'][$newKey]['qty'] + $qty);
    } else {
        $_SESSION['cart'][$newKey] = $built['item'];
    }

    $_SESSION['cart_message'] = 'Đã cập nhật tùy chọn món.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}

/**
 * Tính số tiền được giảm theo loại voucher (phần trăm hoặc số tiền cố định).
 */
function calculateVoucherDiscount(array $voucher, float $subtotal): float
{
    if ($subtotal <= 0 || $subtotal < (float) ($voucher['don_toi_thieu'] ?? 0)) {
        return 0.0;
    }

    $type = (string) ($voucher['kieu_giam'] ?? 'so_tien');
    $value = (float) ($voucher['gia_tri_giam'] ?? 0);
    $discount = ($type === 'phan_tram') ? ($subtotal * ($value / 100.0)) : $value;

    return min($subtotal, max(0.0, round($discount, 2)));
}

/**
 * Kiểm tra điều kiện và lưu voucher vào session.
 */
function cartApplyVoucher(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $code = strtoupper(trim((string) ($_POST['voucher_code'] ?? '')));
    if ($code === '') {
        $_SESSION['cart_message'] = 'Vui lòng nhập mã giảm giá.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $subtotal = 0.0;
    foreach ($cartItems as $item) {
        $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
    }

    $voucher = findActiveVoucherByCode($code);
    if (!$voucher) {
        unset($_SESSION['voucher']);
        $_SESSION['cart_message'] = 'Mã giảm giá không tồn tại hoặc đã ngừng hiệu lực.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    if (!empty($voucher['ngay_het_han']) && strtotime((string) $voucher['ngay_het_han']) < time()) {
        unset($_SESSION['voucher']);
        $_SESSION['cart_message'] = 'Mã giảm giá đã hết hạn.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $minOrder = (float) ($voucher['don_toi_thieu'] ?? 0);
    if ($subtotal < $minOrder) {
        unset($_SESSION['voucher']);
        $_SESSION['cart_message'] = 'Đơn tối thiểu ' . format_vnd($minOrder) . ' mới áp dụng được mã này.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $_SESSION['voucher'] = $voucher;
    $_SESSION['cart_message'] = 'Đã áp dụng mã ' . $voucher['ma_code'] . '.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}

/**
 * Gỡ mã giảm giá đang áp dụng khỏi phiên giỏ hàng.
 */
function cartRemoveVoucher(): void
{
    unset($_SESSION['voucher']);
    $_SESSION['cart_message'] = 'Đã gỡ mã giảm giá.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}
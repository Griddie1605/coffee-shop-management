<?php

require_once __DIR__ . '/../models/ProductModel.php';

function cartAdd(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 200); // Lấy ghi chú từ modal

    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ten_san_pham, gia_goc
         FROM san_pham
         WHERE id = :id AND con_ban = 1
         LIMIT 1"
    );
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['flash_message'] = 'Món không tồn tại hoặc đã ngừng bán.';
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    $sizeId = (int) ($_POST['size_id'] ?? 0);
    $rawToppingIds = $_POST['topping_ids'] ?? [];
    $toppingIds = [];

    if (is_array($rawToppingIds)) {
        foreach ($rawToppingIds as $tid) {
            $idInt = (int) $tid;
            if ($idInt > 0) {
                $toppingIds[$idInt] = $idInt;
            }
        }
    }
    $toppingIds = array_values($toppingIds);
    sort($toppingIds);

    $unitPrice = (float) $product['gia_goc'];
    $sizeName = '';

    // Kiểm tra size hợp lệ thuộc đúng sản phẩm
    if ($sizeId > 0) {
        $stmtSize = $pdo->prepare(
            "SELECT id, ten_size, gia_cong_them
             FROM bien_the_san_pham
             WHERE id = :id AND san_pham_id = :product_id AND con_ban = 1
             LIMIT 1"
        );
        $stmtSize->execute([
            ':id' => $sizeId,
            ':product_id' => $productId,
        ]);
        $sizeRow = $stmtSize->fetch();
        if ($sizeRow) {
            $sizeName = (string) $sizeRow['ten_size'];
            $unitPrice += (float) $sizeRow['gia_cong_them'];
        } else {
            $sizeId = 0;
        }
    }

    $toppingNames = [];
    // Lấy giá topping chuẩn từ DB để cộng vào đơn giá
    if (!empty($toppingIds)) {
        $placeholders = implode(',', array_fill(0, count($toppingIds), '?'));
        $stmtTop = $pdo->prepare(
            "SELECT id, ten_topping, gia_ban
             FROM topping
             WHERE id IN ($placeholders) AND con_ban = 1
             ORDER BY id ASC"
        );
        $stmtTop->execute($toppingIds);
        $topRows = $stmtTop->fetchAll();

        $toppingIds = [];
        foreach ($topRows as $top) {
            $toppingIds[] = (int) $top['id'];
            $toppingNames[] = (string) $top['ten_topping'];
            $unitPrice += (float) $top['gia_ban'];
        }
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Tách dòng nếu khác sản phẩm, size, topping hoặc ghi chú
    $noteHash = $note !== '' ? md5($note) : '0';
    $key = 'p_' . $productId . '_s_' . $sizeId . '_t_' . (!empty($toppingIds) ? implode('-', $toppingIds) : '0') . '_n_' . $noteHash;

    $displayName = (string) $product['ten_san_pham'];
    if ($sizeName !== '') {
        $displayName .= ' (Size ' . $sizeName . ')';
    }
    if (!empty($toppingNames)) {
        $displayName .= ' + ' . implode(', ', $toppingNames);
    }

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = min(50, (int) $_SESSION['cart'][$key]['qty'] + $qty);
    } else {
        // Chỉ lưu 1 item duy nhất, ghi chú nằm riêng trong trường 'note'
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'size_id' => $sizeId,
            'topping_ids' => $toppingIds,
            'name' => $displayName,
            'note' => $note,
            'price' => $unitPrice,
            'qty' => $qty,
        ];
    }

    $_SESSION['flash_message'] = 'Đã thêm "' . $product['ten_san_pham'] . '" (x' . $qty . ') vào giỏ hàng.';
    header('Location: ' . BASE_URL . '/?c=home&a=menu');
    exit;
}

function cartIndex(): void
{
    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $totalAmount = 0.0;
    $sizesByProduct = [];

    foreach ($cartItems as $item) {
        $totalAmount += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
        $pid = (int) ($item['product_id'] ?? 0);
        if ($pid > 0 && !isset($sizesByProduct[$pid])) {
            // Lay danh sach size theo tung mon co trong gio
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

#Cập nhật số lượng và Xoá món
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

function cartEdit(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $oldKey = (string) ($_POST['old_key'] ?? '');
    if ($oldKey === '' || !isset($_SESSION['cart'][$oldKey])) {
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $currentItem = $_SESSION['cart'][$oldKey];
    $productId = (int) ($currentItem['product_id'] ?? 0);
    $qty = max(1, min(50, (int) ($_POST['qty'] ?? 1)));
    $note = mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 200);

    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ten_san_pham, gia_goc
         FROM san_pham
         WHERE id = :id AND con_ban = 1
         LIMIT 1"
    );
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if (!$product) {
        unset($_SESSION['cart'][$oldKey]);
        $_SESSION['cart_message'] = 'Món không còn bán nên đã xóa khỏi giỏ.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $sizeId = (int) ($_POST['size_id'] ?? 0);
    $rawToppingIds = $_POST['topping_ids'] ?? [];
    $toppingIds = [];

    if (is_array($rawToppingIds)) {
        foreach ($rawToppingIds as $tid) {
            $idInt = (int) $tid;
            if ($idInt > 0) {
                $toppingIds[$idInt] = $idInt;
            }
        }
    }
    $toppingIds = array_values($toppingIds);
    sort($toppingIds);

    $unitPrice = (float) $product['gia_goc'];
    $sizeName = '';

    // Kiểm tra size hợp lệ thuộc đúng sản phẩm
    if ($sizeId > 0) {
        $stmtSize = $pdo->prepare(
            "SELECT id, ten_size, gia_cong_them
             FROM bien_the_san_pham
             WHERE id = :id AND san_pham_id = :product_id AND con_ban = 1
             LIMIT 1"
        );
        $stmtSize->execute([
            ':id' => $sizeId,
            ':product_id' => $productId,
        ]);
        $sizeRow = $stmtSize->fetch();
        if ($sizeRow) {
            $sizeName = (string) $sizeRow['ten_size'];
            $unitPrice += (float) $sizeRow['gia_cong_them'];
        } else {
            $sizeId = 0;
        }
    }

    $toppingNames = [];
    // Lấy giá topping chuẩn từ DB
    if (!empty($toppingIds)) {
        $placeholders = implode(',', array_fill(0, count($toppingIds), '?'));
        $stmtTop = $pdo->prepare(
            "SELECT id, ten_topping, gia_ban
             FROM topping
             WHERE id IN ($placeholders) AND con_ban = 1
             ORDER BY id ASC"
        );
        $stmtTop->execute($toppingIds);
        $topRows = $stmtTop->fetchAll();

        $toppingIds = [];
        foreach ($topRows as $top) {
            $toppingIds[] = (int) $top['id'];
            $toppingNames[] = (string) $top['ten_topping'];
            $unitPrice += (float) $top['gia_ban'];
        }
    }

    $noteHash = $note !== '' ? md5($note) : '0';
    $newKey = 'p_' . $productId . '_s_' . $sizeId . '_t_' . (!empty($toppingIds) ? implode('-', $toppingIds) : '0') . '_n_' . $noteHash;

    $displayName = (string) $product['ten_san_pham'];
    if ($sizeName !== '') {
        $displayName .= ' (Size ' . $sizeName . ')';
    }
    if (!empty($toppingNames)) {
        $displayName .= ' + ' . implode(', ', $toppingNames);
    }

    // Xóa dòng cũ trước khi ghi vào key mới để tránh trùng dòng
    unset($_SESSION['cart'][$oldKey]);

    if (isset($_SESSION['cart'][$newKey])) {
        $_SESSION['cart'][$newKey]['qty'] = min(50, (int) $_SESSION['cart'][$newKey]['qty'] + $qty);
    } else {
        $_SESSION['cart'][$newKey] = [
            'product_id' => $productId,
            'size_id' => $sizeId,
            'topping_ids' => $toppingIds,
            'name' => $displayName,
            'note' => $note,
            'price' => $unitPrice,
            'qty' => $qty,
        ];
    }

    $_SESSION['cart_message'] = 'Đã cập nhật tùy chọn món.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}


function calculateVoucherDiscount(array $voucher, float $subtotal): float
{
    if ($subtotal <= 0) {
        return 0.0;
    }

    $minOrder = (float) ($voucher['don_toi_thieu'] ?? 0); // Kiểm tra giá trị đơn tối thiểu[cite: 12]
    if ($subtotal < $minOrder) {
        return 0.0;
    }

    $type = (string) ($voucher['kieu_giam'] ?? 'so_tien'); // 'phan_tram' hoặc 'so_tien'[cite: 12]
    $value = (float) ($voucher['gia_tri_giam'] ?? 0);

    if ($type === 'phan_tram') {
        $discount = $subtotal * ($value / 100.0);
    } else {
        $discount = $value;
    }

    return min($subtotal, max(0.0, round($discount, 2)));
}

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

    $pdo = db_connect();
    // Chỉ lấy mã còn hiệu lực và chưa hết hạn (nếu có ngày hết hạn)[cite: 12]
    $stmt = $pdo->prepare(
        "SELECT id, ma_code, kieu_giam, gia_tri_giam, don_toi_thieu, ngay_het_han, con_hieu_luc
         FROM ma_giam_gia
         WHERE UPPER(ma_code) = :code AND con_hieu_luc = 1
         LIMIT 1"
    );
    $stmt->execute([':code' => $code]);
    $voucher = $stmt->fetch();

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

    $minOrder = (float) ($voucher['don_toi_thieu'] ?? 0); //[cite: 12]
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

function cartRemoveVoucher(): void
{
    unset($_SESSION['voucher']);
    $_SESSION['cart_message'] = 'Đã gỡ mã giảm giá.';
    header('Location: ' . BASE_URL . '/?c=cart&a=index');
    exit;
}
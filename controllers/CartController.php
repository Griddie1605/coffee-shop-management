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
        $_SESSION['flash_message'] = 'Món bạn chọn không tồn tại hoặc đã ngừng bán.';
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $key = 'p_' . $product['id'];
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => (int) $product['id'],
            'name' => (string) $product['ten_san_pham'],
            'price' => (float) $product['gia_goc'],
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

    foreach ($cartItems as $item) {
        $totalAmount += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
    }

    render('cart/index', [
        'pageTitle' => 'Giỏ hàng - 47th cafe',
        'cartItems' => $cartItems,
        'totalAmount' => $totalAmount,
    ]);
}

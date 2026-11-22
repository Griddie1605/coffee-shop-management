<?php

require_once __DIR__ . '/../models/ProductModel.php';

function checkoutCalculateSummary(array $cartItems): array
{
    $subtotal = 0.0;
    foreach ($cartItems as $item) {
        $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
    }

    $discount = 0.0;
    $voucher = isset($_SESSION['voucher']) && is_array($_SESSION['voucher']) ? $_SESSION['voucher'] : null;
    $voucherId = null;

    if ($voucher !== null && $subtotal > 0) {
        $expired = !empty($voucher['ngay_het_han']) && strtotime((string) $voucher['ngay_het_han']) < time();
        $minOrder = (float) ($voucher['don_toi_thieu'] ?? 0);
        if (!$expired && $subtotal >= $minOrder) {
            $voucherId = (int) ($voucher['id'] ?? 0) ?: null;
            if (($voucher['kieu_giam'] ?? 'so_tien') === 'phan_tram') {
                $discount = $subtotal * (((float) ($voucher['gia_tri_giam'] ?? 0)) / 100.0);
            } else {
                $discount = (float) ($voucher['gia_tri_giam'] ?? 0);
            }
            $discount = min($subtotal, max(0.0, round($discount, 2)));
        } else {
            unset($_SESSION['voucher']);
            $voucher = null;
        }
    }

    $finalTotal = max(0.0, $subtotal - $discount);

    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'finalTotal' => $finalTotal,
        'voucher' => $voucher,
        'voucherId' => $voucherId,
    ];
}

function checkoutIndex(): void
{
    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cartItems)) {
        $_SESSION['cart_message'] = 'Giỏ hàng đang trống, hãy chọn món trước khi thanh toán.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $summary = checkoutCalculateSummary($cartItems);
    $userId = $_SESSION['user']['id'] ?? null;

    render('checkout/index', [
        'pageTitle' => 'Thanh toán - 47th cafe',
        'cartItems' => $cartItems,
        'subtotal' => $summary['subtotal'],
        'discountAmount' => $summary['discount'],
        'finalAmount' => $summary['finalTotal'],
        'appliedVoucher' => $summary['voucher'],
        'oldInput' => [
            'ten_nguoi_nhan' => $_SESSION['user']['ho_ten'] ?? '',
            'sdt_nguoi_nhan' => $_SESSION['user']['so_dien_thoai'] ?? '',
            'hinh_thuc_nhan' => 'giao_tan_noi',
            'dia_chi_giao' => $_SESSION['user']['dia_chi'] ?? '',
            'phuong_thuc_thanh_toan' => 'tien_mat',
            'ghi_chu' => '',
        ],
        'errorMessage' => $_SESSION['checkout_error'] ?? '',
        'userId' => $userId,
    ]);
    unset($_SESSION['checkout_error']);
}

function checkoutPlaceOrder(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=checkout&a=index');
        exit;
    }

    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cartItems)) {
        $_SESSION['cart_message'] = 'Giỏ hàng đang trống.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $tenNguoiNhan = trim((string) ($_POST['ten_nguoi_nhan'] ?? ''));
    $sdtNguoiNhan = trim((string) ($_POST['sdt_nguoi_nhan'] ?? ''));
    $hinhThucNhan = (string) ($_POST['hinh_thuc_nhan'] ?? 'giao_tan_noi');
    $diaChiGiao = trim((string) ($_POST['dia_chi_giao'] ?? ''));
    $phuongThucTT = (string) ($_POST['phuong_thuc_thanh_toan'] ?? 'tien_mat');
    $ghiChu = mb_substr(trim((string) ($_POST['ghi_chu'] ?? '')), 0, 500);

    if (!in_array($hinhThucNhan, ['giao_tan_noi', 'den_quan_lay'], true)) {
        $hinhThucNhan = 'giao_tan_noi';
    }
    if (!in_array($phuongThucTT, ['tien_mat', 'chuyen_khoan'], true)) {
        $phuongThucTT = 'tien_mat';
    }

    if ($tenNguoiNhan === '' || $sdtNguoiNhan === '') {
        $_SESSION['checkout_error'] = 'Vui lòng nhập đầy đủ họ tên và số điện thoại người nhận.';
        header('Location: ' . BASE_URL . '/?c=checkout&a=index');
        exit;
    }

    if ($hinhThucNhan === 'giao_tan_noi' && $diaChiGiao === '') {
        $_SESSION['checkout_error'] = 'Vui lòng nhập địa chỉ giao hàng khi chọn giao tận nơi.';
        header('Location: ' . BASE_URL . '/?c=checkout&a=index');
        exit;
    }

    $summary = checkoutCalculateSummary($cartItems);
    $nguoiDungId = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    $maDonHang = 'DH' . date('ymdHis') . random_int(10, 99);

    $pdo = db_connect();

    try {
        $pdo->beginTransaction();

        $stmtOrder = $pdo->prepare(
            "INSERT INTO don_hang (
                ma_don_hang, nguoi_dung_id, ma_giam_gia_id,
                ten_nguoi_nhan, sdt_nguoi_nhan, hinh_thuc_nhan, dia_chi_giao,
                phuong_thuc_thanh_toan, tam_tinh, tien_giam, tong_thanh_toan,
                trang_thai_don, da_tru_kho, ghi_chu
            ) VALUES (
                :ma_don_hang, :nguoi_dung_id, :ma_giam_gia_id,
                :ten_nguoi_nhan, :sdt_nguoi_nhan, :hinh_thuc_nhan, :dia_chi_giao,
                :phuong_thuc_thanh_toan, :tam_tinh, :tien_giam, :tong_thanh_toan,
                'cho_xac_nhan', 0, :ghi_chu
            )"
        );

        $stmtOrder->execute([
            ':ma_don_hang' => $maDonHang,
            ':nguoi_dung_id' => $nguoiDungId,
            ':ma_giam_gia_id' => $summary['voucherId'],
            ':ten_nguoi_nhan' => $tenNguoiNhan,
            ':sdt_nguoi_nhan' => $sdtNguoiNhan,
            ':hinh_thuc_nhan' => $hinhThucNhan,
            ':dia_chi_giao' => $hinhThucNhan === 'giao_tan_noi' ? $diaChiGiao : null,
            ':phuong_thuc_thanh_toan' => $phuongThucTT,
            ':tam_tinh' => $summary['subtotal'],
            ':tien_giam' => $summary['discount'],
            ':tong_thanh_toan' => $summary['finalTotal'],
            ':ghi_chu' => $ghiChu !== '' ? $ghiChu : null,
        ]);

        $donHangId = (int) $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare(
            "INSERT INTO chi_tiet_don_hang (
                don_hang_id, san_pham_id, bien_the_id,
                ten_san_pham, ten_size, don_gia, so_luong, thanh_tien
            ) VALUES (
                :don_hang_id, :san_pham_id, :bien_the_id,
                :ten_san_pham, :ten_size, :don_gia, :so_luong, :thanh_tien
            )"
        );

        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $sizeId = (int) ($item['size_id'] ?? 0);
            $unitPrice = (float) ($item['price'] ?? 0);
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $lineTotal = round($unitPrice * $qty, 2);

            $rawName = (string) ($item['name'] ?? 'Món');
            $tenSize = null;
            if (preg_match('/\(Size\s+([^)]+)\)/u', $rawName, $mSize)) {
                $tenSize = mb_substr(trim($mSize[1]), 0, 30);
            }

            // Gộp ghi chú món vào tên món lưu trong chi tiết đơn vì bảng chưa có cột ghi chú riêng
            $itemNote = trim((string) ($item['note'] ?? ''));
            $fullItemName = $itemNote !== '' ? ($rawName . ' [Ghi chú: ' . $itemNote . ']') : $rawName;
            $fullItemName = mb_substr($fullItemName, 0, 150);

            $stmtDetail->execute([
                ':don_hang_id' => $donHangId,
                ':san_pham_id' => $productId,
                ':bien_the_id' => $sizeId > 0 ? $sizeId : null,
                ':ten_san_pham' => $fullItemName,
                ':ten_size' => $tenSize,
                ':don_gia' => $unitPrice,
                ':so_luong' => $qty,
                ':thanh_tien' => $lineTotal,
            ]);
        }

        $pdo->commit();

        unset($_SESSION['cart'], $_SESSION['voucher']);
        $_SESSION['last_order_code'] = $maDonHang;

        header('Location: ' . BASE_URL . '/?c=checkout&a=success');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['checkout_error'] = 'Không thể lưu đơn hàng lúc này. Vui lòng thử lại.';
        header('Location: ' . BASE_URL . '/?c=checkout&a=index');
        exit;
    }
}

function checkoutSuccess(): void
{
    $orderCode = (string) ($_SESSION['last_order_code'] ?? '');
    if ($orderCode === '') {
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    render('checkout/success', [
        'pageTitle' => 'Đặt hàng thành công - 47th cafe',
        'orderCode' => $orderCode,
    ]);
}
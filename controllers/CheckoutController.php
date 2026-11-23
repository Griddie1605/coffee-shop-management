<?php

require_once __DIR__ . '/../models/OrderModel.php';

/**
 * Tính tạm tính, giảm giá hợp lệ và tổng thanh toán cuối cùng trước khi tạo đơn.
 */
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

    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'finalTotal' => max(0.0, $subtotal - $discount),
        'voucher' => $voucher,
        'voucherId' => $voucherId,
    ];
}

/**
 * Hiển thị trang xác nhận thông tin giao nhận và tóm tắt đơn hàng.
 */
function checkoutIndex(): void
{
    $cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cartItems)) {
        $_SESSION['cart_message'] = 'Giỏ hàng đang trống, hãy chọn món trước khi thanh toán.';
        header('Location: ' . BASE_URL . '/?c=cart&a=index');
        exit;
    }

    $summary = checkoutCalculateSummary($cartItems);

    render('checkout/index', [
        'pageTitle' => 'Thanh toán - CF Song Thư',
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
        'userId' => $_SESSION['user']['id'] ?? null,
    ]);
    unset($_SESSION['checkout_error']);
}

/**
 * Kiểm tra dữ liệu người nhận và gọi OrderModel lưu đơn hàng bằng transaction.
 */
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

    try {
        $maDonHang = createOrderWithItems([
            'nguoi_dung_id' => isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null,
            'ma_giam_gia_id' => $summary['voucherId'],
            'ten_nguoi_nhan' => $tenNguoiNhan,
            'sdt_nguoi_nhan' => $sdtNguoiNhan,
            'hinh_thuc_nhan' => $hinhThucNhan,
            'dia_chi_giao' => $hinhThucNhan === 'giao_tan_noi' ? $diaChiGiao : null,
            'phuong_thuc_thanh_toan' => $phuongThucTT,
            'tam_tinh' => $summary['subtotal'],
            'tien_giam' => $summary['discount'],
            'tong_thanh_toan' => $summary['finalTotal'],
            'ghi_chu' => $ghiChu !== '' ? $ghiChu : null,
        ], $cartItems);

        unset($_SESSION['cart'], $_SESSION['voucher']);
        $_SESSION['last_order_code'] = $maDonHang;

        header('Location: ' . BASE_URL . '/?c=checkout&a=success');
        exit;
    } catch (Throwable $e) {
        $_SESSION['checkout_error'] = 'Không thể lưu đơn hàng lúc này. Vui lòng thử lại.' . $e->getMessage();
        header('Location: ' . BASE_URL . '/?c=checkout&a=index');
        exit;
    }
}

/**
 * Trang thông báo đặt hàng thành công kèm mã đơn vừa tạo.
 */
function checkoutSuccess(): void
{
    $orderCode = (string) ($_SESSION['last_order_code'] ?? '');
    if ($orderCode === '') {
        header('Location: ' . BASE_URL . '/?c=home&a=menu');
        exit;
    }

    render('checkout/success', [
        'pageTitle' => 'Đặt hàng thành công - CF Song Thư',
        'orderCode' => $orderCode,
    ]);
}
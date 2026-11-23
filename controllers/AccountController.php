<?php

require_once __DIR__ . '/../models/UserModel.php';

/**
 * Yêu cầu đăng nhập trước khi vào trang quản lý tài khoản.
 */
function accountRequireLogin(): int
{
    if (empty($_SESSION['user']['id'])) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login&redirect=account');
        exit;
    }
    return (int) $_SESSION['user']['id'];
}

/**
 * Hiển thị trang quản lý tài khoản (cập nhật thông tin & đổi mật khẩu).
 */
function accountIndex(): void
{
    $userId = accountRequireLogin();
    $user = findUserById($userId);

    if (!$user) {
        unset($_SESSION['user']);
        header('Location: ' . BASE_URL . '/?c=auth&a=login');
        exit;
    }

    render('account/index', [
        'pageTitle' => 'Tài khoản của tôi - CF Song Thư',
        'user' => $user,
        'profileSuccess' => $_SESSION['account_profile_success'] ?? '',
        'profileError' => $_SESSION['account_profile_error'] ?? '',
        'passwordSuccess' => $_SESSION['account_password_success'] ?? '',
        'passwordError' => $_SESSION['account_password_error'] ?? '',
    ]);

    unset(
        $_SESSION['account_profile_success'],
        $_SESSION['account_profile_error'],
        $_SESSION['account_password_success'],
        $_SESSION['account_password_error']
    );
}

/**
 * Cập nhật họ tên, số điện thoại và địa chỉ mặc định; đồng bộ lại $_SESSION['user'].
 */
function accountUpdateProfile(): void
{
    $userId = accountRequireLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    $hoTen = trim((string) ($_POST['ho_ten'] ?? ''));
    $soDienThoai = trim((string) ($_POST['so_dien_thoai'] ?? ''));
    $diaChi = trim((string) ($_POST['dia_chi'] ?? ''));

    if ($hoTen === '') {
        $_SESSION['account_profile_error'] = 'Vui lòng nhập họ và tên.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    if (mb_strlen($soDienThoai) > 20) {
        $_SESSION['account_profile_error'] = 'Số điện thoại không được vượt quá 20 ký tự.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    if (updateUserProfile($userId, $hoTen, $soDienThoai, $diaChi)) {
        $_SESSION['user']['ho_ten'] = $hoTen;
        $_SESSION['user']['so_dien_thoai'] = $soDienThoai;
        $_SESSION['user']['dia_chi'] = $diaChi;
        $_SESSION['account_profile_success'] = 'Đã cập nhật thông tin tài khoản.';
    } else {
        $_SESSION['account_profile_error'] = 'Không thể cập nhật thông tin lúc này.';
    }

    header('Location: ' . BASE_URL . '/?c=account&a=index');
    exit;
}

/**
 * Đổi mật khẩu tài khoản sau khi xác thực mật khẩu hiện tại.
 */
function accountChangePassword(): void
{
    $userId = accountRequireLogin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $_SESSION['account_password_error'] = 'Vui lòng nhập đầy đủ mật khẩu hiện tại và mật khẩu mới.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['account_password_error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['account_password_error'] = 'Mật khẩu mới và nhập lại mật khẩu không khớp.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    $user = findUserById($userId);
    if (!$user || !password_verify($currentPassword, (string) $user['mat_khau'])) {
        $_SESSION['account_password_error'] = 'Mật khẩu hiện tại không chính xác.';
        header('Location: ' . BASE_URL . '/?c=account&a=index');
        exit;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    if (updateUserPassword($userId, $hash)) {
        $_SESSION['account_password_success'] = 'Đã đổi mật khẩu thành công.';
    } else {
        $_SESSION['account_password_error'] = 'Không thể đổi mật khẩu lúc này.';
    }

    header('Location: ' . BASE_URL . '/?c=account&a=index');
    exit;
}
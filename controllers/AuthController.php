<?php

function authLogin(): void
{
    if (!empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/?c=home&a=index');
        exit;
    }

    $error = '';
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
        } else {
            $pdo = db_connect();
            $stmt = $pdo->prepare(
                "SELECT id, ho_ten, email, so_dien_thoai, mat_khau, dia_chi, vai_tro, trang_thai_hoat_dong
                 FROM nguoi_dung
                 WHERE email = :email
                 LIMIT 1"
            );
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string) $user['mat_khau'])) {
                $error = 'Email hoặc mật khẩu không chính xác.';
            } elseif ((int) $user['trang_thai_hoat_dong'] !== 1) {
                $error = 'Tài khoản hiện đang bị khóa hoặc ngừng hoạt động.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'ho_ten' => (string) $user['ho_ten'],
                    'email' => (string) $user['email'],
                    'so_dien_thoai' => (string) ($user['so_dien_thoai'] ?? ''),
                    'dia_chi' => (string) ($user['dia_chi'] ?? ''),
                    'vai_tro' => (string) $user['vai_tro'],
                ];

                $redirect = (string) ($_GET['redirect'] ?? '');
                if ($redirect === 'checkout') {
                    header('Location: ' . BASE_URL . '/?c=checkout&a=index');
                } elseif ($redirect === 'orders') {
                    header('Location: ' . BASE_URL . '/?c=order&a=index');
                } else {
                    header('Location: ' . BASE_URL . '/?c=home&a=menu');
                }
                exit;
            }
        }
    }

    render('auth/login', [
        'pageTitle' => 'Đăng nhập - 47th cafe',
        'error' => $error,
        'email' => $email,
    ]);
}

function authRegister(): void
{
    if (!empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/?c=home&a=index');
        exit;
    }

    $error = '';
    $old = [
        'ho_ten' => '',
        'email' => '',
        'so_dien_thoai' => '',
        'dia_chi' => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old['ho_ten'] = trim((string) ($_POST['ho_ten'] ?? ''));
        $old['email'] = trim((string) ($_POST['email'] ?? ''));
        $old['so_dien_thoai'] = trim((string) ($_POST['so_dien_thoai'] ?? ''));
        $old['dia_chi'] = trim((string) ($_POST['dia_chi'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($old['ho_ten'] === '' || $old['email'] === '' || $password === '') {
            $error = 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Định dạng email không hợp lệ.';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } else {
            $pdo = db_connect();
            $checkStmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE email = :email LIMIT 1");
            $checkStmt->execute([':email' => $old['email']]);

            if ($checkStmt->fetch()) {
                $error = 'Email này đã được sử dụng.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare(
                    "INSERT INTO nguoi_dung (
                        ho_ten, email, so_dien_thoai, mat_khau, dia_chi, vai_tro, trang_thai_hoat_dong
                    ) VALUES (
                        :ho_ten, :email, :so_dien_thoai, :mat_khau, :dia_chi, 'khach_hang', 1
                    )"
                );
                $insertStmt->execute([
                    ':ho_ten' => $old['ho_ten'],
                    ':email' => $old['email'],
                    ':so_dien_thoai' => $old['so_dien_thoai'] !== '' ? $old['so_dien_thoai'] : null,
                    ':mat_khau' => $hashedPassword,
                    ':dia_chi' => $old['dia_chi'] !== '' ? $old['dia_chi'] : null,
                ]);

                $newId = (int) $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $newId,
                    'ho_ten' => $old['ho_ten'],
                    'email' => $old['email'],
                    'so_dien_thoai' => $old['so_dien_thoai'],
                    'dia_chi' => $old['dia_chi'],
                    'vai_tro' => 'khach_hang',
                ];

                header('Location: ' . BASE_URL . '/?c=home&a=menu');
                exit;
            }
        }
    }

    render('auth/register', [
        'pageTitle' => 'Đăng ký tài khoản - 47th cafe',
        'error' => $error,
        'old' => $old,
    ]);
}

function authLogout(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
    header('Location: ' . BASE_URL . '/?c=home&a=index');
    exit;
}
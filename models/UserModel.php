<?php

/**
 * Tìm tài khoản theo email phục vụ đăng nhập hoặc kiểm tra trùng email khi đăng ký.
 */
function findUserByEmail(string $email): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ho_ten, email, so_dien_thoai, mat_khau, dia_chi, vai_tro, trang_thai_hoat_dong
         FROM nguoi_dung
         WHERE email = :email
         LIMIT 1"
    );
    $stmt->execute([':email' => trim($email)]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Tạo tài khoản khách hàng mới và trả về ID vừa tạo.
 */
function createCustomerUser(array $data): int
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO nguoi_dung (
            ho_ten, email, so_dien_thoai, mat_khau, dia_chi, vai_tro, trang_thai_hoat_dong
         ) VALUES (
            :ho_ten, :email, :so_dien_thoai, :mat_khau, :dia_chi, 'khach_hang', 1
         )"
    );
    $stmt->execute([
        ':ho_ten' => $data['ho_ten'],
        ':email' => $data['email'],
        ':so_dien_thoai' => $data['so_dien_thoai'] !== '' ? $data['so_dien_thoai'] : null,
        ':mat_khau' => $data['mat_khau'],
        ':dia_chi' => $data['dia_chi'] !== '' ? $data['dia_chi'] : null,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Lấy danh sách tài khoản khách hàng kèm số đơn và tổng chi tiêu (không tính đơn đã hủy).
 */
function getCustomersWithStats(string $keyword = ''): array
{
    $pdo = db_connect();
    $sql = "SELECT
                nd.id,
                nd.ho_ten,
                nd.email,
                nd.so_dien_thoai,
                nd.dia_chi,
                nd.trang_thai_hoat_dong,
                nd.ngay_tao,
                COUNT(dh.id) AS total_orders,
                COALESCE(SUM(CASE WHEN dh.trang_thai_don != 'da_huy' THEN dh.tong_thanh_toan ELSE 0 END), 0) AS total_spent
            FROM nguoi_dung nd
            LEFT JOIN don_hang dh ON dh.nguoi_dung_id = nd.id
            WHERE nd.vai_tro = 'khach_hang'";
    $params = [];

    $keyword = trim($keyword);
    if ($keyword !== '') {
        $sql .= " AND (nd.ho_ten LIKE :kw_name OR nd.email LIKE :kw_email OR nd.so_dien_thoai LIKE :kw_phone)";
        $like = '%' . $keyword . '%';
        $params[':kw_name'] = $like;
        $params[':kw_email'] = $like;
        $params[':kw_phone'] = $like;
    }

    $sql .= " GROUP BY nd.id, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.dia_chi, nd.trang_thai_hoat_dong, nd.ngay_tao
              ORDER BY nd.ngay_tao DESC, nd.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Lấy thông tin cơ bản của một khách hàng theo ID.
 */
function findCustomerById(int $customerId): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ho_ten, email, so_dien_thoai, dia_chi, trang_thai_hoat_dong, ngay_tao
         FROM nguoi_dung
         WHERE id = :id AND vai_tro = 'khach_hang'
         LIMIT 1"
    );
    $stmt->execute([':id' => $customerId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Đảo trạng thái hoạt động (mở / khóa) của tài khoản khách hàng.
 */
function toggleCustomerActiveStatus(int $customerId): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "UPDATE nguoi_dung
         SET trang_thai_hoat_dong = IF(trang_thai_hoat_dong = 1, 0, 1)
         WHERE id = :id AND vai_tro = 'khach_hang'"
    );
    $stmt->execute([':id' => $customerId]);
}

/**
 * Lấy thông tin đầy đủ của người dùng theo ID.
 */
function findUserById(int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, ho_ten, email, so_dien_thoai, mat_khau, dia_chi, vai_tro, trang_thai_hoat_dong
         FROM nguoi_dung
         WHERE id = :id
         LIMIT 1"
    );
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Cập nhật họ tên, số điện thoại và địa chỉ mặc định của tài khoản.
 */
function updateUserProfile(int $userId, string $hoTen, string $soDienThoai, string $diaChi): bool
{
    if ($userId <= 0) {
        return false;
    }
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "UPDATE nguoi_dung
         SET ho_ten = :ho_ten,
             so_dien_thoai = :so_dien_thoai,
             dia_chi = :dia_chi
         WHERE id = :id"
    );
    return $stmt->execute([
        ':ho_ten' => $hoTen,
        ':so_dien_thoai' => $soDienThoai !== '' ? $soDienThoai : null,
        ':dia_chi' => $diaChi !== '' ? $diaChi : null,
        ':id' => $userId,
    ]);
}

/**
 * Cập nhật mật khẩu đã băm cho tài khoản.
 */
function updateUserPassword(int $userId, string $hashedPassword): bool
{
    if ($userId <= 0) {
        return false;
    }
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "UPDATE nguoi_dung
         SET mat_khau = :mat_khau
         WHERE id = :id"
    );
    return $stmt->execute([
        ':mat_khau' => $hashedPassword,
        ':id' => $userId,
    ]);
}


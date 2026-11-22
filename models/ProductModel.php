<?php

/**
 * Lấy nhóm sản phẩm nổi bật để hiển thị trên trang chủ.
 * Chỉ lấy món và danh mục đang còn bán để tránh quảng bá nội dung không khả dụng.
 */
function getFeaturedProducts(int $limit = 6): array
{
    $pdo = db_connect();

    $sql = "SELECT sp.id, sp.ten_san_pham, sp.gia_goc, sp.mo_ta, dm.ten_danh_muc, dm.loai_mon
            FROM san_pham sp
            INNER JOIN danh_muc dm ON sp.danh_muc_id = dm.id
            WHERE sp.con_ban = 1 AND dm.con_ban = 1
            ORDER BY sp.id ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lấy danh mục đang hoạt động, sắp theo thứ tự cấu hình.
 */
function getAllCategories(): array
{
    $pdo = db_connect();

    $sql = "SELECT id, ten_danh_muc, loai_mon, thu_tu_hien_thi
            FROM danh_muc
            WHERE con_ban = 1
            ORDER BY thu_tu_hien_thi ASC, id ASC";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Lọc thực đơn theo danh mục và từ khóa mà không thay đổi route hay tên bảng CSDL.
 * Tham số tìm kiếm được truyền qua PDO để tránh nối chuỗi và SQL injection.
 */
function getFilteredProducts(?int $categoryId, string $keyword = ''): array
{
    $pdo = db_connect();

    $sql = "SELECT p.*, d.ten_danh_muc
            FROM san_pham p
            INNER JOIN danh_muc d ON p.danh_muc_id = d.id
            WHERE p.con_ban = 1
              AND d.con_ban = 1";
    $params = [];

    if ($categoryId !== null && $categoryId > 0) {
        $sql .= " AND p.danh_muc_id = :category_id";
        $params[':category_id'] = $categoryId;
    }

    $keyword = trim($keyword);
    if ($keyword !== '') {
        $sql .= " AND (p.ten_san_pham LIKE :kw_name)";
        $like = '%' . $keyword . '%';
        $params[':kw_name'] = $like;
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Lấy size còn bán của một sản phẩm.
 * Size mặc định được ưu tiên vì đây là lựa chọn an toàn với người dùng chưa tùy chỉnh.
 */
function getProductSizes(int $productId): array
{
    $pdo = db_connect();
    // Ưu tiên size mặc định trước, sau đó theo id
    $stmt = $pdo->prepare(
        "SELECT id, san_pham_id, ten_size, gia_cong_them, la_mac_dinh
         FROM bien_the_san_pham
         WHERE san_pham_id = :product_id AND con_ban = 1
         ORDER BY la_mac_dinh DESC, id ASC"
    );
    $stmt->execute([':product_id' => $productId]);

    return $stmt->fetchAll();
}

/**
 * Lấy topping còn hiệu lực để tính giá tại giỏ hàng và modal đặt món.
 */
function getActiveToppings(): array
{
    $pdo = db_connect();
    $stmt = $pdo->query(
        "SELECT id, ten_topping, gia_ban
         FROM topping
         WHERE con_ban = 1
         ORDER BY id ASC"
    );

    return $stmt->fetchAll();
}

/**
 * Kiểm tra slug đã tồn tại trong bảng chỉ định hay chưa (có thể loại trừ bản ghi đang sửa).
 */
function isSlugExists(string $table, string $slug, ?int $ignoreId = null): bool
{
    $allowedTables = ['danh_muc', 'san_pham'];
    if (!in_array($table, $allowedTables, true)) {
        return false;
    }

    $pdo = db_connect();
    $sql = "SELECT id FROM {$table} WHERE duong_dan = :slug";
    $params = [':slug' => $slug];

    if ($ignoreId !== null && $ignoreId > 0) {
        $sql .= " AND id != :id";
        $params[':id'] = $ignoreId;
    }
    $sql .= " LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
}

/**
 * Lấy toàn bộ danh mục (bao gồm cả danh mục ngưng bán) phục vụ trang quản trị.
 */
function getAllCategoriesForAdmin(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT id, ten_danh_muc, duong_dan, loai_mon, thu_tu_hien_thi, con_ban
         FROM danh_muc
         ORDER BY thu_tu_hien_thi ASC, id ASC"
    )->fetchAll();
}

/**
 * Kiểm tra danh mục có tồn tại theo ID hay không.
 */
function findCategoryById(int $categoryId): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT id, ten_danh_muc FROM danh_muc WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $categoryId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Thêm mới hoặc cập nhật danh mục.
 */
function saveCategoryRecord(array $data, int $id = 0): void
{
    $pdo = db_connect();
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE danh_muc
             SET ten_danh_muc = :ten, duong_dan = :slug, loai_mon = :loai,
                 thu_tu_hien_thi = :thu_tu, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':ten' => $data['ten_danh_muc'],
            ':slug' => $data['duong_dan'],
            ':loai' => $data['loai_mon'],
            ':thu_tu' => $data['thu_tu_hien_thi'],
            ':con_ban' => $data['con_ban'],
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO danh_muc (ten_danh_muc, duong_dan, loai_mon, thu_tu_hien_thi, con_ban)
             VALUES (:ten, :slug, :loai, :thu_tu, :con_ban)"
        );
        $stmt->execute([
            ':ten' => $data['ten_danh_muc'],
            ':slug' => $data['duong_dan'],
            ':loai' => $data['loai_mon'],
            ':thu_tu' => $data['thu_tu_hien_thi'],
            ':con_ban' => $data['con_ban'],
        ]);
    }
}

/**
 * Đảo trạng thái còn bán của danh mục.
 */
function toggleCategoryActive(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE danh_muc SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Lấy toàn bộ sản phẩm kèm tên/loại danh mục phục vụ trang quản trị.
 */
function getAllProductsForAdmin(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT sp.id, sp.danh_muc_id, sp.ten_san_pham, sp.duong_dan, sp.mo_ta,
                sp.hinh_anh, sp.gia_goc, sp.con_ban, sp.ngay_tao,
                dm.ten_danh_muc, dm.loai_mon
         FROM san_pham sp
         INNER JOIN danh_muc dm ON dm.id = sp.danh_muc_id
         ORDER BY sp.id DESC"
    )->fetchAll();
}

/**
 * Kiểm tra sản phẩm có tồn tại theo ID hay không.
 */
function findProductById(int $productId): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT id, ten_san_pham FROM san_pham WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $productId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Thêm mới hoặc cập nhật sản phẩm.
 */
function saveProductRecord(array $data, int $id = 0): void
{
    $pdo = db_connect();
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE san_pham
             SET danh_muc_id = :dm_id, ten_san_pham = :ten, duong_dan = :slug,
                 mo_ta = :mo_ta, hinh_anh = :hinh_anh, gia_goc = :gia_goc, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':dm_id' => $data['danh_muc_id'],
            ':ten' => $data['ten_san_pham'],
            ':slug' => $data['duong_dan'],
            ':mo_ta' => $data['mo_ta'],
            ':hinh_anh' => $data['hinh_anh'],
            ':gia_goc' => $data['gia_goc'],
            ':con_ban' => $data['con_ban'],
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO san_pham (danh_muc_id, ten_san_pham, duong_dan, mo_ta, hinh_anh, gia_goc, con_ban)
             VALUES (:dm_id, :ten, :slug, :mo_ta, :hinh_anh, :gia_goc, :con_ban)"
        );
        $stmt->execute([
            ':dm_id' => $data['danh_muc_id'],
            ':ten' => $data['ten_san_pham'],
            ':slug' => $data['duong_dan'],
            ':mo_ta' => $data['mo_ta'],
            ':hinh_anh' => $data['hinh_anh'],
            ':gia_goc' => $data['gia_goc'],
            ':con_ban' => $data['con_ban'],
        ]);
    }
}

/**
 * Đảo trạng thái còn bán của sản phẩm.
 */
function toggleProductActive(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE san_pham SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Lấy toàn bộ size kèm tên món để hiển thị ở trang quản trị.
 */
function getAllSizesForAdmin(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT bt.id, bt.san_pham_id, bt.ten_size, bt.gia_cong_them, bt.la_mac_dinh, bt.con_ban,
                sp.ten_san_pham
         FROM bien_the_san_pham bt
         INNER JOIN san_pham sp ON sp.id = bt.san_pham_id
         ORDER BY sp.ten_san_pham ASC, bt.la_mac_dinh DESC, bt.id ASC"
    )->fetchAll();
}

/**
 * Thêm mới hoặc cập nhật size; nếu chọn làm size mặc định thì bỏ cờ mặc định ở các size cùng món.
 */
function saveSizeRecord(array $data, int $id = 0): void
{
    $pdo = db_connect();
    $productId = (int) $data['san_pham_id'];

    if ((int) $data['la_mac_dinh'] === 1) {
        $stmtReset = $pdo->prepare("UPDATE bien_the_san_pham SET la_mac_dinh = 0 WHERE san_pham_id = :pid");
        $stmtReset->execute([':pid' => $productId]);
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE bien_the_san_pham
             SET san_pham_id = :pid, ten_size = :ten_size, gia_cong_them = :gia,
                 la_mac_dinh = :mac_dinh, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':pid' => $productId,
            ':ten_size' => $data['ten_size'],
            ':gia' => $data['gia_cong_them'],
            ':mac_dinh' => $data['la_mac_dinh'],
            ':con_ban' => $data['con_ban'],
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO bien_the_san_pham (san_pham_id, ten_size, gia_cong_them, la_mac_dinh, con_ban)
             VALUES (:pid, :ten_size, :gia, :mac_dinh, :con_ban)"
        );
        $stmt->execute([
            ':pid' => $productId,
            ':ten_size' => $data['ten_size'],
            ':gia' => $data['gia_cong_them'],
            ':mac_dinh' => $data['la_mac_dinh'],
            ':con_ban' => $data['con_ban'],
        ]);
    }
}

/**
 * Đảo trạng thái áp dụng của size.
 */
function toggleSizeActive(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE bien_the_san_pham SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Lấy toàn bộ topping phục vụ trang quản trị.
 */
function getAllToppingsForAdmin(): array
{
    $pdo = db_connect();
    return $pdo->query("SELECT id, ten_topping, gia_ban, con_ban FROM topping ORDER BY id ASC")->fetchAll();
}

/**
 * Thêm mới hoặc cập nhật topping.
 */
function saveToppingRecord(array $data, int $id = 0): void
{
    $pdo = db_connect();
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE topping
             SET ten_topping = :ten, gia_ban = :gia, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':ten' => $data['ten_topping'],
            ':gia' => $data['gia_ban'],
            ':con_ban' => $data['con_ban'],
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO topping (ten_topping, gia_ban, con_ban)
             VALUES (:ten, :gia, :con_ban)"
        );
        $stmt->execute([
            ':ten' => $data['ten_topping'],
            ':gia' => $data['gia_ban'],
            ':con_ban' => $data['con_ban'],
        ]);
    }
}

/**
 * Đảo trạng thái bán của topping.
 */
function toggleToppingActive(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE topping SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

<?php

require_once __DIR__ . '/../models/ProductModel.php';

function adminRequireStaff(): array
{
    $user = $_SESSION['user'] ?? null;
    if (empty($user) || !in_array((string) ($user['vai_tro'] ?? ''), ['quan_tri', 'nhan_vien'], true)) {
        header('Location: ' . BASE_URL . '/?c=auth&a=login');
        exit;
    }
    return $user;
}

function adminIndex(): void
{
    adminRequireStaff();

    $statusFilter = (string) ($_GET['status'] ?? 'all');
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];

    $pdo = db_connect();
    $sql = "SELECT id, ma_don_hang, nguoi_dung_id, ten_nguoi_nhan, sdt_nguoi_nhan,
                   hinh_thuc_nhan, dia_chi_giao, phuong_thuc_thanh_toan,
                   tam_tinh, tien_giam, tong_thanh_toan, trang_thai_don,
                   ghi_chu, ngay_tao
            FROM don_hang";
    $params = [];

    if ($statusFilter !== 'all' && in_array($statusFilter, $allowedStatuses, true)) {
        $sql .= " WHERE trang_thai_don = :status";
        $params[':status'] = $statusFilter;
    } else {
        $statusFilter = 'all';
    }

    $sql .= " ORDER BY ngay_tao DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    $itemsByOrder = [];
    if (!empty($orders)) {
        $orderIds = array_map(static fn($row) => (int) $row['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmtItems = $pdo->prepare(
            "SELECT don_hang_id, ten_san_pham, ten_size, don_gia, so_luong, thanh_tien
             FROM chi_tiet_don_hang
             WHERE don_hang_id IN ($placeholders)
             ORDER BY id ASC"
        );
        $stmtItems->execute($orderIds);
        foreach ($stmtItems->fetchAll() as $item) {
            $itemsByOrder[(int) $item['don_hang_id']][] = $item;
        }
    }

    render('admin/index', [
        'pageTitle' => 'Quản trị đơn hàng - 47th cafe',
        'orders' => $orders,
        'itemsByOrder' => $itemsByOrder,
        'statusFilter' => $statusFilter,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

function adminUpdateStatus(): void
{
    adminRequireStaff();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=index');
        exit;
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = (string) ($_POST['trang_thai_don'] ?? '');
    $allowedStatuses = ['cho_xac_nhan', 'dang_pha_che', 'dang_giao', 'hoan_thanh', 'da_huy'];

    if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
        $_SESSION['admin_error'] = 'Dữ liệu cập nhật trạng thái đơn không hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=index');
        exit;
    }

    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE don_hang SET trang_thai_don = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $orderId,
    ]);

    $_SESSION['admin_success'] = 'Đã cập nhật trạng thái đơn hàng.';
    $redirectStatus = (string) ($_POST['return_status'] ?? 'all');
    header('Location: ' . BASE_URL . '/?c=admin&a=index' . ($redirectStatus !== 'all' ? '&status=' . urlencode($redirectStatus) : ''));
    exit;
}

function adminMakeSlug(string $text): string
{
    $map = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        'đ'=>'d',
    ];
    $lower = mb_strtolower(trim($text), 'UTF-8');
    $converted = strtr($lower, $map);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $converted) ?? '';
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'item';
}

function adminUniqueSlug(PDO $pdo, string $table, string $baseName, ?int $ignoreId = null): string
{
    $baseSlug = adminMakeSlug($baseName);
    $slug = $baseSlug;
    $suffix = 2;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE duong_dan = :slug";
        $params = [':slug' => $slug];
        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= " AND id != :id";
            $params[':id'] = $ignoreId;
        }
        $sql .= " LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
}

function adminCatalog(): void
{
    adminRequireStaff();

    $pdo = db_connect();
    $categories = $pdo->query(
        "SELECT id, ten_danh_muc, duong_dan, loai_mon, thu_tu_hien_thi, con_ban
         FROM danh_muc
         ORDER BY thu_tu_hien_thi ASC, id ASC"
    )->fetchAll();

    $products = $pdo->query(
        "SELECT sp.id, sp.danh_muc_id, sp.ten_san_pham, sp.duong_dan, sp.mo_ta,
                sp.hinh_anh, sp.gia_goc, sp.con_ban, sp.ngay_tao,
                dm.ten_danh_muc, dm.loai_mon
         FROM san_pham sp
         INNER JOIN danh_muc dm ON dm.id = sp.danh_muc_id
         ORDER BY sp.id DESC"
    )->fetchAll();

    $editCategory = null;
    if (!empty($_GET['edit_cat'])) {
        $catId = (int) $_GET['edit_cat'];
        foreach ($categories as $c) {
            if ((int) $c['id'] === $catId) {
                $editCategory = $c;
                break;
            }
        }
    }

    $editProduct = null;
    if (!empty($_GET['edit_prod'])) {
        $prodId = (int) $_GET['edit_prod'];
        foreach ($products as $p) {
            if ((int) $p['id'] === $prodId) {
                $editProduct = $p;
                break;
            }
        }
    }

    $sizes = $pdo->query(
    "SELECT bt.id, bt.san_pham_id, bt.ten_size, bt.gia_cong_them, bt.la_mac_dinh, bt.con_ban,
            sp.ten_san_pham
     FROM bien_the_san_pham bt
     INNER JOIN san_pham sp ON sp.id = bt.san_pham_id
     ORDER BY sp.ten_san_pham ASC, bt.la_mac_dinh DESC, bt.id ASC"
    )->fetchAll();

    $toppings = $pdo->query(
        "SELECT id, ten_topping, gia_ban, con_ban
        FROM topping
        ORDER BY id ASC"
    )->fetchAll();

    $editSize = null;
    if (!empty($_GET['edit_size'])) {
        $sizeId = (int) $_GET['edit_size'];
        foreach ($sizes as $s) {
            if ((int) $s['id'] === $sizeId) {
                $editSize = $s;
                break;
            }
        }
    }

    $editTopping = null;
    if (!empty($_GET['edit_topping'])) {
        $toppingId = (int) $_GET['edit_topping'];
        foreach ($toppings as $t) {
            if ((int) $t['id'] === $toppingId) {
                $editTopping = $t;
                break;
            }
        }
    }

    render('admin/catalog', [
        'pageTitle' => 'Quản lý danh mục & sản phẩm - 47th cafe',
        'categories' => $categories,
        'products' => $products,
        'editCategory' => $editCategory,
        'editProduct' => $editProduct,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
        'sizes' => $sizes,
        'toppings' => $toppings,
        'editSize' => $editSize,
        'editTopping' => $editTopping,
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

function adminSaveCategory(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $ten = trim((string) ($_POST['ten_danh_muc'] ?? ''));
    $loaiMon = (string) ($_POST['loai_mon'] ?? 'do_uong');
    $thuTu = (int) ($_POST['thu_tu_hien_thi'] ?? 0);
    $conBan = isset($_POST['con_ban']) ? 1 : 0;

    if ($ten === '' || !in_array($loaiMon, ['do_uong', 'banh'], true)) {
        $_SESSION['admin_error'] = 'Tên danh mục hoặc loại món không hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $pdo = db_connect();
    $slug = adminUniqueSlug($pdo, 'danh_muc', $ten, $id > 0 ? $id : null);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE danh_muc
             SET ten_danh_muc = :ten, duong_dan = :slug, loai_mon = :loai,
                 thu_tu_hien_thi = :thu_tu, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':ten' => $ten,
            ':slug' => $slug,
            ':loai' => $loaiMon,
            ':thu_tu' => $thuTu,
            ':con_ban' => $conBan,
            ':id' => $id,
        ]);
        $_SESSION['admin_success'] = 'Đã cập nhật danh mục.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO danh_muc (ten_danh_muc, duong_dan, loai_mon, thu_tu_hien_thi, con_ban)
             VALUES (:ten, :slug, :loai, :thu_tu, :con_ban)"
        );
        $stmt->execute([
            ':ten' => $ten,
            ':slug' => $slug,
            ':loai' => $loaiMon,
            ':thu_tu' => $thuTu,
            ':con_ban' => $conBan,
        ]);
        $_SESSION['admin_success'] = 'Đã thêm danh mục mới.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
}

function adminSaveProduct(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $danhMucId = (int) ($_POST['danh_muc_id'] ?? 0);
    $ten = trim((string) ($_POST['ten_san_pham'] ?? ''));
    $giaGoc = (float) ($_POST['gia_goc'] ?? 0);
    $moTa = trim((string) ($_POST['mo_ta'] ?? ''));
    $existingImage = trim((string) ($_POST['hinh_anh_hien_tai'] ?? ''));
    $conBan = isset($_POST['con_ban']) ? 1 : 0;

    if ($ten === '' || $danhMucId <= 0 || $giaGoc < 0) {
        $_SESSION['admin_error'] = 'Vui lòng nhập tên sản phẩm, chọn danh mục và giá hợp lệ.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $pdo = db_connect();
    $checkCat = $pdo->prepare("SELECT id FROM danh_muc WHERE id = :id LIMIT 1");
    $checkCat->execute([':id' => $danhMucId]);
    if (!$checkCat->fetch()) {
        $_SESSION['admin_error'] = 'Danh mục đã chọn không tồn tại.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $uploadResult = adminUploadProductImage($_FILES['hinh_anh_file'] ?? null);
    if (!$uploadResult['ok']) {
        $_SESSION['admin_error'] = (string) ($uploadResult['error'] ?? 'Lỗi tải ảnh.');
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $hinhAnh = $uploadResult['path'] ?? ($existingImage !== '' ? $existingImage : null);
    $slug = adminUniqueSlug($pdo, 'san_pham', $ten, $id > 0 ? $id : null);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE san_pham
             SET danh_muc_id = :dm_id, ten_san_pham = :ten, duong_dan = :slug,
                 mo_ta = :mo_ta, hinh_anh = :hinh_anh, gia_goc = :gia_goc, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':dm_id' => $danhMucId,
            ':ten' => $ten,
            ':slug' => $slug,
            ':mo_ta' => $moTa !== '' ? $moTa : null,
            ':hinh_anh' => $hinhAnh,
            ':gia_goc' => $giaGoc,
            ':con_ban' => $conBan,
            ':id' => $id,
        ]);
        $_SESSION['admin_success'] = 'Đã cập nhật sản phẩm.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO san_pham (danh_muc_id, ten_san_pham, duong_dan, mo_ta, hinh_anh, gia_goc, con_ban)
             VALUES (:dm_id, :ten, :slug, :mo_ta, :hinh_anh, :gia_goc, :con_ban)"
        );
        $stmt->execute([
            ':dm_id' => $danhMucId,
            ':ten' => $ten,
            ':slug' => $slug,
            ':mo_ta' => $moTa !== '' ? $moTa : null,
            ':hinh_anh' => $hinhAnh,
            ':gia_goc' => $giaGoc,
            ':con_ban' => $conBan,
        ]);
        $_SESSION['admin_success'] = 'Đã thêm sản phẩm mới.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
}

function adminToggleProduct(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE san_pham SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của sản phẩm.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
}

function adminDashboard(): void
{
    adminRequireStaff();

    $pdo = db_connect();

    $orderStats = $pdo->query(
        "SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN trang_thai_don = 'cho_xac_nhan' THEN 1 ELSE 0 END) AS pending_orders,
            SUM(CASE WHEN trang_thai_don = 'dang_pha_che' THEN 1 ELSE 0 END) AS brewing_orders,
            SUM(CASE WHEN trang_thai_don = 'hoan_thanh' THEN tong_thanh_toan ELSE 0 END) AS completed_revenue
         FROM don_hang"
    )->fetch() ?: [];

    $catalogStats = $pdo->query(
        "SELECT
            (SELECT COUNT(*) FROM danh_muc WHERE con_ban = 1) AS active_categories,
            (SELECT COUNT(*) FROM san_pham WHERE con_ban = 1) AS active_products,
            (SELECT COUNT(*) FROM san_pham) AS total_products"
    )->fetch() ?: [];

    $recentOrders = $pdo->query(
        "SELECT id, ma_don_hang, ten_nguoi_nhan, tong_thanh_toan, trang_thai_don, ngay_tao
         FROM don_hang
         ORDER BY ngay_tao DESC, id DESC
         LIMIT 6"
    )->fetchAll();

    render('admin/dashboard', [
        'pageTitle' => 'Admin Dashboard - 47th cafe',
        'orderStats' => $orderStats,
        'catalogStats' => $catalogStats,
        'recentOrders' => $recentOrders,
    ]);
}

function adminUploadProductImage(?array $file): array
{
    if (empty($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Tải ảnh lên thất bại (mã lỗi ' . (int) $file['error'] . ').'];
    }

    $maxBytes = 3 * 1024 * 1024; // 3MB
    if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > $maxBytes) {
        return ['ok' => false, 'error' => 'Kích thước ảnh phải nhỏ hơn hoặc bằng 3MB.'];
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpPath) ?: '';

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'Chỉ hỗ trợ file ảnh JPG, PNG, WEBP hoặc GIF.'];
    }

    $ext = $allowedMimes[$mime];
    $uploadDir = __DIR__ . '/../assets/images/products';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return ['ok' => false, 'error' => 'Không thể tạo thư mục lưu ảnh sản phẩm.'];
    }

    try {
        $randomHex = bin2hex(random_bytes(6));
    } catch (Throwable $e) {
        $randomHex = uniqid('', true);
    }

    $fileName = 'prod_' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $randomHex) . '.' . $ext;
    $destPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        return ['ok' => false, 'error' => 'Không thể lưu file ảnh vào thư mục dự án.'];
    }

    return ['ok' => true, 'path' => 'assets/images/products/' . $fileName];
}

function adminToggleCategory(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE danh_muc SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của danh mục.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
}

function adminCustomers(): void
{
    adminRequireStaff();

    $keyword = trim((string) ($_GET['q'] ?? ''));
    $selectedCustomerId = (int) ($_GET['customer_id'] ?? 0);

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
    $customers = $stmt->fetchAll();

    $selectedCustomer = null;
    $customerOrders = [];
    $orderItemsByOrderId = [];

    if ($selectedCustomerId > 0) {
        foreach ($customers as $c) {
            if ((int) $c['id'] === $selectedCustomerId) {
                $selectedCustomer = $c;
                break;
            }
        }

        if ($selectedCustomer === null) {
            $stmtSingle = $pdo->prepare(
                "SELECT id, ho_ten, email, so_dien_thoai, dia_chi, trang_thai_hoat_dong, ngay_tao
                 FROM nguoi_dung
                 WHERE id = :id AND vai_tro = 'khach_hang'
                 LIMIT 1"
            );
            $stmtSingle->execute([':id' => $selectedCustomerId]);
            $found = $stmtSingle->fetch();
            if ($found) {
                $selectedCustomer = $found;
            }
        }

        if ($selectedCustomer !== null) {
            $stmtOrders = $pdo->prepare(
                "SELECT id, ma_don_hang, ten_nguoi_nhan, sdt_nguoi_nhan, hinh_thuc_nhan,
                        dia_chi_giao, phuong_thuc_thanh_toan, tam_tinh, tien_giam,
                        tong_thanh_toan, trang_thai_don, ghi_chu, ngay_tao
                 FROM don_hang
                 WHERE nguoi_dung_id = :uid
                 ORDER BY ngay_tao DESC, id DESC"
            );
            $stmtOrders->execute([':uid' => (int) $selectedCustomer['id']]);
            $customerOrders = $stmtOrders->fetchAll();

            if (!empty($customerOrders)) {
                $orderIds = array_map(static fn($o) => (int) $o['id'], $customerOrders);
                $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                $stmtItems = $pdo->prepare(
                    "SELECT don_hang_id, ten_san_pham, ten_size, don_gia, so_luong, thanh_tien
                     FROM chi_tiet_don_hang
                     WHERE don_hang_id IN ($placeholders)
                     ORDER BY id ASC"
                );
                $stmtItems->execute($orderIds);
                foreach ($stmtItems->fetchAll() as $item) {
                    $orderItemsByOrderId[(int) $item['don_hang_id']][] = $item;
                }
            }
        }
    }

    render('admin/customers', [
        'pageTitle' => 'Quản lý khách hàng - 47th cafe',
        'customers' => $customers,
        'keyword' => $keyword,
        'selectedCustomer' => $selectedCustomer,
        'customerOrders' => $customerOrders,
        'orderItemsByOrderId' => $orderItemsByOrderId,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

function adminToggleCustomerStatus(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=customers');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $q = trim((string) ($_POST['q'] ?? ''));
    $selectedId = (int) ($_POST['customer_id'] ?? 0);

    if ($id > 0) {
        $pdo = db_connect();
        $stmt = $pdo->prepare(
            "UPDATE nguoi_dung
             SET trang_thai_hoat_dong = IF(trang_thai_hoat_dong = 1, 0, 1)
             WHERE id = :id AND vai_tro = 'khach_hang'"
        );
        $stmt->execute([':id' => $id]);
        $_SESSION['admin_success'] = 'Đã cập nhật trạng thái tài khoản khách hàng.';
    }

    $query = ['c' => 'admin', 'a' => 'customers'];
    if ($q !== '') {
        $query['q'] = $q;
    }
    if ($selectedId > 0) {
        $query['customer_id'] = $selectedId;
    }

    header('Location: ' . BASE_URL . '/?' . http_build_query($query));
    exit;
}

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

    $pdo = db_connect();
    $checkProd = $pdo->prepare("SELECT id FROM san_pham WHERE id = :id LIMIT 1");
    $checkProd->execute([':id' => $productId]);
    if (!$checkProd->fetch()) {
        $_SESSION['admin_error'] = 'Sản phẩm được chọn không tồn tại.';
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
        exit;
    }

    if ($laMacDinh === 1) {
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
            ':ten_size' => $tenSize,
            ':gia' => $giaCongThem,
            ':mac_dinh' => $laMacDinh,
            ':con_ban' => $conBan,
            ':id' => $id,
        ]);
        $_SESSION['admin_success'] = 'Đã cập nhật size sản phẩm.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO bien_the_san_pham (san_pham_id, ten_size, gia_cong_them, la_mac_dinh, con_ban)
             VALUES (:pid, :ten_size, :gia, :mac_dinh, :con_ban)"
        );
        $stmt->execute([
            ':pid' => $productId,
            ':ten_size' => $tenSize,
            ':gia' => $giaCongThem,
            ':mac_dinh' => $laMacDinh,
            ':con_ban' => $conBan,
        ]);
        $_SESSION['admin_success'] = 'Đã thêm size mới cho sản phẩm.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

function adminToggleSize(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE bien_the_san_pham SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của size.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

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

    $pdo = db_connect();
    if ($id > 0) {
        $stmt = $pdo->prepare(
            "UPDATE topping
             SET ten_topping = :ten, gia_ban = :gia, con_ban = :con_ban
             WHERE id = :id"
        );
        $stmt->execute([
            ':ten' => $tenTopping,
            ':gia' => $giaBan,
            ':con_ban' => $conBan,
            ':id' => $id,
        ]);
        $_SESSION['admin_success'] = 'Đã cập nhật topping.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO topping (ten_topping, gia_ban, con_ban)
             VALUES (:ten, :gia, :con_ban)"
        );
        $stmt->execute([
            ':ten' => $tenTopping,
            ':gia' => $giaBan,
            ':con_ban' => $conBan,
        ]);
        $_SESSION['admin_success'] = 'Đã thêm topping mới.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}

function adminToggleTopping(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE topping SET con_ban = IF(con_ban = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của topping.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog#options-section');
    exit;
}
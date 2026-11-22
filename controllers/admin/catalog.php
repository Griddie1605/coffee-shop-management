<?php

require_once __DIR__ . '/../../models/ProductModel.php';

/**
 * Chuyển chuỗi tiếng Việt có dấu thành slug ASCII phục vụ đường dẫn.
 */
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

/**
 * Sinh slug không trùng lặp trong bảng danh_muc hoặc san_pham.
 */
function adminUniqueSlug(string $table, string $baseName, ?int $ignoreId = null): string
{
    $baseSlug = adminMakeSlug($baseName);
    $slug = $baseSlug;
    $suffix = 2;

    while (isSlugExists($table, $slug, $ignoreId)) {
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

/**
 * Tải dữ liệu danh mục, sản phẩm, size, topping và bản ghi đang sửa (nếu có).
 */
function adminCatalog(): void
{
    adminRequireStaff();

    $categories = getAllCategoriesForAdmin();
    $products = getAllProductsForAdmin();
    $sizes = getAllSizesForAdmin();
    $toppings = getAllToppingsForAdmin();

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
        'sizes' => $sizes,
        'toppings' => $toppings,
        'editCategory' => $editCategory,
        'editProduct' => $editProduct,
        'editSize' => $editSize,
        'editTopping' => $editTopping,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
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

    saveCategoryRecord([
        'ten_danh_muc' => $ten,
        'duong_dan' => adminUniqueSlug('danh_muc', $ten, $id > 0 ? $id : null),
        'loai_mon' => $loaiMon,
        'thu_tu_hien_thi' => $thuTu,
        'con_ban' => $conBan,
    ], $id);

    $_SESSION['admin_success'] = $id > 0 ? 'Đã cập nhật danh mục.' : 'Đã thêm danh mục mới.';
    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
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
        toggleCategoryActive($id);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của danh mục.';
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

    if (findCategoryById($danhMucId) === null) {
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

    saveProductRecord([
        'danh_muc_id' => $danhMucId,
        'ten_san_pham' => $ten,
        'duong_dan' => adminUniqueSlug('san_pham', $ten, $id > 0 ? $id : null),
        'mo_ta' => $moTa !== '' ? $moTa : null,
        'hinh_anh' => $hinhAnh,
        'gia_goc' => $giaGoc,
        'con_ban' => $conBan,
    ], $id);

    $_SESSION['admin_success'] = $id > 0 ? 'Đã cập nhật sản phẩm.' : 'Đã thêm sản phẩm mới.';
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
        toggleProductActive($id);
        $_SESSION['admin_success'] = 'Đã đổi trạng thái bán của sản phẩm.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=catalog');
    exit;
}

/**
 * Kiểm tra định dạng MIME thực qua finfo và lưu ảnh vào assets/images/products.
 */
function adminUploadProductImage(?array $file): array
{
    if (empty($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Tải ảnh lên thất bại (mã lỗi ' . (int) $file['error'] . ').'];
    }

    $maxBytes = 3 * 1024 * 1024;
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
    $uploadDir = __DIR__ . '/../../assets/images/products';
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
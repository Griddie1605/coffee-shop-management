<?php

require_once __DIR__ . '/../../models/InventoryModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';

/**
 * Chuẩn hóa đơn vị chọn trên form ('gói', 'hộp', 'hũ', 'chai') và quy đổi sang đơn vị gốc ('g' / 'ml').
 */
function inventoryUnitSpec(string $selectedUnit, string $name = ''): array
{
    $u = mb_strtolower(trim($selectedUnit), 'UTF-8');
    $n = mb_strtolower(trim($name), 'UTF-8');

    if ($u === 'g' || $u === 'gram') {
        $isJar = str_contains($n, 'mứt') || str_contains($n, 'jam') || str_contains($n, 'kem') || str_contains($n, 'bơ') || str_contains($n, 'phô mai');
        $u = $isJar ? 'hũ' : 'gói';
    } elseif ($u === 'ml') {
        $isBottle = str_contains($n, 'syrup') || str_contains($n, 'siro') || str_contains($n, 'mật ong') || str_contains($n, 'nước đường') || str_contains($n, 'sốt');
        $u = $isBottle ? 'chai' : 'hộp';
    }

    if (!in_array($u, ['gói', 'hộp', 'hũ', 'chai'], true)) {
        $u = 'gói';
    }

    if ($u === 'hộp') {
        return [
            'unit_choice' => 'hộp',
            'base_unit' => 'ml',
            'factor' => 1000.0,
            'note' => '1.000 ml/hộp',
        ];
    }

    if ($u === 'chai') {
        $isSyrup = str_contains($n, 'syrup') || str_contains($n, 'siro');
        $factor = $isSyrup ? 750.0 : 500.0;
        return [
            'unit_choice' => 'chai',
            'base_unit' => 'ml',
            'factor' => $factor,
            'note' => ($isSyrup ? '750' : '500') . ' ml/chai',
        ];
    }

    if ($u === 'hũ') {
        return [
            'unit_choice' => 'hũ',
            'base_unit' => 'g',
            'factor' => 500.0,
            'note' => '500 g/hũ',
        ];
    }

    return [
        'unit_choice' => 'gói',
        'base_unit' => 'g',
        'factor' => 500.0,
        'note' => '500 g/gói',
    ];
}

/**
 * Kiểm tra sản phẩm có đang ngừng bán hay không dựa trên các tên cột trạng thái phổ biến.
 */
function isProductStoppedSelling(array $product): bool
{
    foreach (['trang_thai', 'status', 'is_active', 'hien_thi', 'dang_ban'] as $col) {
        if (array_key_exists($col, $product) && $product[$col] !== null && $product[$col] !== '') {
            $val = $product[$col];
            if (is_string($val)) {
                $lower = mb_strtolower(trim($val), 'UTF-8');
                if (in_array($lower, ['0', 'ngung_ban', 'ngừng bán', 'inactive', 'hidden', 'an', 'ẩn', 'stopped'], true)) {
                    return true;
                }
                if (in_array($lower, ['1', 'dang_ban', 'đang bán', 'active', 'show', 'hien', 'hiện'], true)) {
                    return false;
                }
            }
            return (int) $val === 0;
        }
    }
    return false;
}

function adminInventory(): void
{
    adminRequireStaff();

    $tab = (string) ($_GET['tab'] ?? 'ingredients');
    $kw = trim((string) ($_GET['q'] ?? ''));
    $stockFilter = (string) ($_GET['stock'] ?? 'all');
    $movementType = (string) ($_GET['movement_type'] ?? 'all');
    $recipeProductId = (int) ($_GET['recipe_product_id'] ?? 0);

    $ingredients = getAllIngredients($kw, $stockFilter);
    $products = getAllProductsForAdmin();
    $recipes = getRecipes($recipeProductId);
    $movements = getInventoryMovements($movementType, 60);
    $receipts = getRecentStockInReceipts(15);
    $stats = getInventorySummaryStats();

    $editIngredient = null;
    if (!empty($_GET['edit_nl'])) {
        $editId = (int) $_GET['edit_nl'];
        foreach ($ingredients as $nl) {
            if ((int) $nl['id'] === $editId) {
                $editIngredient = $nl;
                break;
            }
        }
    }

    render('admin/inventory', [
        'pageTitle' => 'Quản lý kho nguyên liệu - CF Song Thư',
        'tab' => $tab,
        'kw' => $kw,
        'stockFilter' => $stockFilter,
        'movementType' => $movementType,
        'recipeProductId' => $recipeProductId,
        'ingredients' => $ingredients,
        'products' => $products,
        'recipes' => $recipes,
        'movements' => $movements,
        'receipts' => $receipts,
        'stats' => $stats,
        'editIngredient' => $editIngredient,
        'flashSuccess' => $_SESSION['admin_success'] ?? '',
        'flashError' => $_SESSION['admin_error'] ?? '',
    ]);
    unset($_SESSION['admin_success'], $_SESSION['admin_error']);
}

function adminSaveIngredient(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $ma = strtoupper(trim((string) ($_POST['ma_nguyen_lieu'] ?? '')));
    $ten = trim((string) ($_POST['ten_nguyen_lieu'] ?? ''));
    $unitChoice = (string) ($_POST['don_vi_tinh'] ?? 'gói');
    $tonDau = max(0.0, (float) ($_POST['ton_kho_hien_tai'] ?? 0));
    $nguongPack = max(0.0, (float) ($_POST['nguong_canh_bao'] ?? 0));
    $trangThai = isset($_POST['trang_thai']) ? 1 : 0;

    if ($ma === '' || $ten === '') {
        $_SESSION['admin_error'] = 'Vui lòng nhập đầy đủ mã nguyên liệu và tên nguyên liệu.';
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory');
        exit;
    }

    $spec = inventoryUnitSpec($unitChoice, $ten);
    $nguongBase = round($nguongPack * (float) $spec['factor'], 3);

    try {
        saveIngredient([
            'ma_nguyen_lieu' => $ma,
            'ten_nguyen_lieu' => $ten,
            'don_vi_tinh' => $spec['base_unit'],
            'ton_kho_hien_tai' => $tonDau,
            'nguong_canh_bao' => $nguongBase,
            'trang_thai' => $trangThai,
        ], $id);
        $_SESSION['admin_success'] = $id > 0 ? 'Đã cập nhật nguyên liệu.' : 'Đã thêm nguyên liệu mới.';
    } catch (Throwable $e) {
        $_SESSION['admin_error'] = 'Không thể lưu nguyên liệu (kiểm tra mã nguyên liệu có bị trùng không).';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=inventory');
    exit;
}

function adminDeleteIngredient(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && deleteIngredientById($id)) {
        $_SESSION['admin_success'] = 'Đã xóa nguyên liệu.';
    } else {
        $_SESSION['admin_error'] = 'Không thể xóa nguyên liệu đã có công thức, phiếu nhập hoặc lịch sử kho.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=inventory');
    exit;
}

function adminStockIn(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
        exit;
    }

    $tenNcc = trim((string) ($_POST['ten_nha_cung_cap'] ?? ''));
    $ghiChu = trim((string) ($_POST['ghi_chu'] ?? ''));
    $nlIds = $_POST['nguyen_lieu_id'] ?? [];
    $qtysPack = $_POST['so_luong'] ?? [];
    $pricesPack = $_POST['don_gia_nhap'] ?? [];

    $ingredientMap = [];
    foreach (getAllIngredients('', 'all') as $nl) {
        $ingredientMap[(int) $nl['id']] = $nl;
    }

    $items = [];
    if (is_array($nlIds)) {
        foreach ($nlIds as $idx => $rawNlId) {
            $nlId = (int) $rawNlId;
            $qtyPack = (float) ($qtysPack[$idx] ?? 0);
            $pricePerPack = max(0.0, (float) ($pricesPack[$idx] ?? 0));

            if ($nlId > 0 && $qtyPack > 0 && isset($ingredientMap[$nlId])) {
                $nl = $ingredientMap[$nlId];
                $spec = inventoryUnitSpec((string) $nl['don_vi_tinh'], (string) $nl['ten_nguyen_lieu']);
                $factor = max(0.001, (float) $spec['factor']);

                $qtyBase = round($qtyPack * $factor, 3);
                $pricePerBaseUnit = round($pricePerPack / $factor, 4);

                $items[] = [
                    'nguyen_lieu_id' => $nlId,
                    'so_luong' => $qtyBase,
                    'don_gia_nhap' => $pricePerBaseUnit,
                ];
            }
        }
    }

    if (empty($items)) {
        $_SESSION['admin_error'] = 'Vui lòng chọn ít nhất một nguyên liệu và số lượng nhập lớn hơn 0.';
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
        exit;
    }

    try {
        $maPhieu = createStockInReceipt([
            'ten_nha_cung_cap' => $tenNcc,
            'nguoi_tao_id' => (int) ($_SESSION['user']['id'] ?? 0) ?: null,
            'ghi_chu' => $ghiChu,
        ], $items);
        $_SESSION['admin_success'] = 'Đã tạo phiếu nhập kho #' . $maPhieu . ' và cộng tồn kho.';
    } catch (Throwable $e) {
        $_SESSION['admin_error'] = 'Lỗi khi tạo phiếu nhập kho: ' . $e->getMessage();
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
    exit;
}

function adminStockOut(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
        exit;
    }

    $nlId = (int) ($_POST['nguyen_lieu_id'] ?? 0);
    $qtyInput = (float) ($_POST['so_luong'] ?? 0);
    $unitMode = mb_strtolower(trim((string) ($_POST['don_vi_xuat_huy'] ?? 'base')), 'UTF-8');
    $lyDo = trim((string) ($_POST['ly_do'] ?? ''));

    if ($nlId <= 0 || $qtyInput <= 0) {
        $_SESSION['admin_error'] = 'Vui lòng chọn nguyên liệu và số lượng xuất hủy lớn hơn 0.';
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
        exit;
    }

    $ingredientMap = [];
    foreach (getAllIngredients('', 'all') as $nl) {
        $ingredientMap[(int) $nl['id']] = $nl;
    }

    $qtyBase = $qtyInput;
    if (isset($ingredientMap[$nlId]) && $unitMode === 'pack') {
        $nl = $ingredientMap[$nlId];
        $spec = inventoryUnitSpec((string) $nl['don_vi_tinh'], (string) $nl['ten_nguyen_lieu']);
        $factor = max(0.001, (float) $spec['factor']);
        $qtyBase = round($qtyInput * $factor, 3);
    } else {
        $qtyBase = round($qtyInput, 3);
    }

    try {
        $maThamChieu = createManualStockOut($nlId, $qtyBase, $lyDo);
        $_SESSION['admin_success'] = 'Đã ghi nhận xuất hủy kho #' . $maThamChieu . '.';
    } catch (Throwable $e) {
        $_SESSION['admin_error'] = $e->getMessage();
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=stock');
    exit;
}

function adminSaveRecipe(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes');
        exit;
    }

    $productId = (int) ($_POST['san_pham_id'] ?? 0);
    $nlIds = $_POST['nguyen_lieu_id'] ?? [];
    $dinhLuongs = $_POST['dinh_luong'] ?? [];
    $ghiChus = $_POST['ghi_chu'] ?? [];

    if ($productId <= 0) {
        $_SESSION['admin_error'] = 'Vui lòng chọn sản phẩm.';
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes');
        exit;
    }

    if (!is_array($nlIds)) {
        $nlIds = [$nlIds];
        $dinhLuongs = [$dinhLuongs];
        $ghiChus = [$ghiChus];
    }

    $savedCount = 0;
    foreach ($nlIds as $idx => $rawNlId) {
        $ingredientId = (int) $rawNlId;
        $dinhLuong = (float) ($dinhLuongs[$idx] ?? 0);
        $ghiChu = trim((string) ($ghiChus[$idx] ?? ''));

        if ($ingredientId > 0 && $dinhLuong > 0) {
            saveRecipeItem($productId, $ingredientId, $dinhLuong, $ghiChu);
            $savedCount++;
        }
    }

    if ($savedCount === 0) {
        $_SESSION['admin_error'] = 'Vui lòng chọn ít nhất một nguyên liệu và định lượng lớn hơn 0.';
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes&recipe_product_id=' . $productId);
        exit;
    }

    $_SESSION['admin_success'] = 'Đã lưu ' . $savedCount . ' dòng công thức pha chế cho sản phẩm.';
    header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes&recipe_product_id=' . $productId);
    exit;
}

function adminDeleteRecipe(): void
{
    adminRequireStaff();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes');
        exit;
    }

    $recipeId = (int) ($_POST['id'] ?? 0);
    $pid = (int) ($_POST['recipe_product_id'] ?? 0);
    if ($recipeId > 0) {
        deleteRecipeItem($recipeId);
        $_SESSION['admin_success'] = 'Đã xóa nguyên liệu khỏi công thức.';
    }

    header('Location: ' . BASE_URL . '/?c=admin&a=inventory&tab=recipes' . ($pid > 0 ? '&recipe_product_id=' . $pid : ''));
    exit;
}
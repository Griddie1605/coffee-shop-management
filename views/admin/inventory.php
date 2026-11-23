<?php
$activeTab = (string) ($tab ?? 'ingredients');
if (!in_array($activeTab, ['ingredients', 'stock', 'recipes'], true)) {
    $activeTab = 'ingredients';
}

$typeMeta = [
    'nhap_kho' => ['Nhập kho', 'bg-success'],
    'xuat_ban' => ['Xuất bán', 'bg-primary'],
    'hoan_don' => ['Hoàn đơn', 'bg-info text-dark'],
    'xuat_huy' => ['Xuất hủy', 'bg-danger'],
];

function format_qty_clean(float $val): string
{
    $rounded = round($val, 3);
    if (abs($rounded - round($rounded)) < 0.00001) {
        return number_format((float) round($rounded), 0, ',', '.');
    }
    $formatted = number_format($rounded, 3, ',', '.');
    return rtrim(rtrim($formatted, '0'), ',');
}

function format_qty_input(float $val): string
{
    $rounded = round($val, 3);
    if (abs($rounded - round($rounded)) < 0.00001) {
        return (string) (int) round($rounded);
    }
    return rtrim(rtrim(number_format($rounded, 3, '.', ''), '0'), '.');
}

function get_unit_spec(string $unitOrChoice, string $name = ''): array
{
    if (function_exists('inventoryUnitSpec')) {
        return inventoryUnitSpec($unitOrChoice, $name);
    }
    $u = mb_strtolower(trim($unitOrChoice), 'UTF-8');
    if ($u === 'ml' || $u === 'hộp') {
        return ['unit_choice' => 'hộp', 'base_unit' => 'ml', 'factor' => 1000.0, 'note' => '1.000 ml/hộp'];
    }
    return ['unit_choice' => 'gói', 'base_unit' => 'g', 'factor' => 500.0, 'note' => '500 g/gói'];
}

function format_pack_summary(float $tonBase, float $nguongBase, string $baseUnit, string $name = ''): string
{
    $spec = get_unit_spec($baseUnit, $name);
    $factor = max(0.001, (float) $spec['factor']);
    $choice = (string) $spec['unit_choice'];
    $note = (string) $spec['note'];

    $tonPack = round($tonBase / $factor, 3);
    $nguongPack = round($nguongBase / $factor, 3);

    return '~' . format_qty_clean($tonPack) . ' ' . $choice
        . ' (' . $note . ') · Ngưỡng: ' . format_qty_clean($nguongPack) . ' ' . $choice;
}

function format_product_option_label(array $product): string
{
    $name = (string) ($product['ten_san_pham'] ?? '');
    $isStopped = function_exists('isProductStoppedSelling') ? isProductStoppedSelling($product) : false;
    return $isStopped ? ($name . ' (Ngừng bán)') : $name;
}

$editName = (string) ($editIngredient['ten_nguyen_lieu'] ?? '');
$editSpec = isset($editIngredient)
    ? get_unit_spec((string) ($editIngredient['don_vi_tinh'] ?? 'g'), $editName)
    : get_unit_spec('gói', '');
$editUnitChoice = (string) $editSpec['unit_choice'];
$editThresholdPack = isset($editIngredient)
    ? round(((float) ($editIngredient['nguong_canh_bao'] ?? 0)) / max(0.001, (float) $editSpec['factor']), 3)
    : 2.0;
?>

<div class="mb-4">
    <h1 class="h2 mb-1">Quản lý kho nguyên liệu &amp; Công thức pha chế</h1>
    <p class="mb-0 text-coffee-muted">Theo dõi tồn kho, phiếu nhập/xuất kho và định lượng nguyên liệu theo từng món</p>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success border-coffee-soft" role="alert">
        <?= htmlspecialchars((string) $flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger border-coffee-soft" role="alert">
        <?= htmlspecialchars((string) $flashError) ?>
    </div>
<?php endif; ?>

<!-- 4 THẺ THỐNG KÊ -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Tổng nguyên liệu</div>
            <div class="h4 fw-bold mt-1 mb-0"><?= (int) ($stats['total_ingredients'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Sắp hết hàng</div>
            <div class="h4 fw-bold mt-1 mb-0 text-warning"><?= (int) ($stats['low_stock_count'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Đã hết hàng</div>
            <div class="h4 fw-bold mt-1 mb-0 text-danger"><?= (int) ($stats['out_of_stock_count'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Tổng nhập / Tổng xuất</div>
            <div class="fs-6 fw-bold mt-2 mb-0">
                +<?= format_qty_clean((float) ($stats['total_imported_qty'] ?? 0)) ?> /
                -<?= format_qty_clean((float) ($stats['total_exported_qty'] ?? 0)) ?>
            </div>
        </div>
    </div>
</div>

<!-- ĐIỀU HƯỚNG TAB -->
<ul class="nav nav-pills gap-2 mb-4">
    <li class="nav-item">
        <a
            href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=ingredients"
            class="nav-link rounded-pill px-3 <?= $activeTab === 'ingredients' ? 'bg-dark text-white fw-semibold' : 'text-dark border border-coffee-soft bg-white' ?>"
        >
            Nguyên liệu &amp; Tồn kho
        </a>
    </li>
    <li class="nav-item">
        <a
            href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=stock"
            class="nav-link rounded-pill px-3 <?= $activeTab === 'stock' ? 'bg-dark text-white fw-semibold' : 'text-dark border border-coffee-soft bg-white' ?>"
        >
            Nhập / Xuất kho
        </a>
    </li>
    <li class="nav-item">
        <a
            href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=recipes"
            class="nav-link rounded-pill px-3 <?= $activeTab === 'recipes' ? 'bg-dark text-white fw-semibold' : 'text-dark border border-coffee-soft bg-white' ?>"
        >
            Công thức pha chế
        </a>
    </li>
</ul>

<?php if ($activeTab === 'ingredients'): ?>
    <div class="row g-4 align-items-start">
        <div class="col-xl-4">
            <div class="card card-coffee p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">
                        <?= !empty($editIngredient) ? 'Sửa nguyên liệu #' . (int) $editIngredient['id'] : 'Thêm nguyên liệu' ?>
                    </h2>
                    <?php if (!empty($editIngredient)): ?>
                        <a href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=ingredients" class="small text-coffee-muted">Hủy</a>
                    <?php endif; ?>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=saveIngredient">
                    <?php if (!empty($editIngredient)): ?>
                        <input type="hidden" name="id" value="<?= (int) $editIngredient['id'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="ton_kho_hien_tai" value="0">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mã nguyên liệu *</label>
                        <input
                            type="text"
                            name="ma_nguyen_lieu"
                            required
                            maxlength="30"
                            class="form-control border-coffee-soft"
                            placeholder="VD: NL006"
                            value="<?= htmlspecialchars((string) ($editIngredient['ma_nguyen_lieu'] ?? '')) ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tên nguyên liệu *</label>
                        <input
                            type="text"
                            id="nlTenInput"
                            name="ten_nguyen_lieu"
                            required
                            maxlength="120"
                            class="form-control border-coffee-soft"
                            placeholder="VD: Syrup Caramel"
                            value="<?= htmlspecialchars($editName) ?>"
                        >
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Đơn vị tính *</label>
                            <select id="nlUnitSelect" name="don_vi_tinh" required class="form-select border-coffee-soft">
                                <option value="gói" <?= $editUnitChoice === 'gói' ? 'selected' : '' ?>>gói</option>
                                <option value="hộp" <?= $editUnitChoice === 'hộp' ? 'selected' : '' ?>>hộp</option>
                                <option value="hũ" <?= $editUnitChoice === 'hũ' ? 'selected' : '' ?>>hũ</option>
                                <option value="chai" <?= $editUnitChoice === 'chai' ? 'selected' : '' ?>>chai</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Ngưỡng cảnh báo</label>
                            <input
                                type="number"
                                step="any"
                                min="0"
                                name="nguong_canh_bao"
                                class="form-control border-coffee-soft"
                                placeholder="VD: 2"
                                value="<?= htmlspecialchars(format_qty_input($editThresholdPack)) ?>"
                            >
                        </div>
                    </div>

                    <div id="nlConvertHint" class="small text-coffee-muted mb-3"></div>

                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="trang_thai"
                            id="nlTrangThai"
                            value="1"
                            <?= (!isset($editIngredient) || (int) ($editIngredient['trang_thai'] ?? 1) === 1) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label small" for="nlTrangThai">Đang sử dụng</label>
                    </div>

                    <button type="submit" class="btn btn-moss rounded-pill px-4 w-100">
                        <?= !empty($editIngredient) ? 'Lưu cập nhật' : 'Thêm nguyên liệu' ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card card-coffee p-4">
                <form method="GET" action="<?= BASE_URL ?>/" class="row g-2 mb-3">
                    <input type="hidden" name="c" value="admin">
                    <input type="hidden" name="a" value="inventory">
                    <input type="hidden" name="tab" value="ingredients">
                    <div class="col-sm-5">
                        <input
                            type="text"
                            name="q"
                            class="form-control form-control-sm border-coffee-soft"
                            placeholder="Tìm mã hoặc tên nguyên liệu..."
                            value="<?= htmlspecialchars((string) ($kw ?? '')) ?>"
                        >
                    </div>
                    <div class="col-sm-4">
                        <select name="stock" class="form-select form-select-sm border-coffee-soft">
                            <option value="all" <?= ($stockFilter ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả tồn kho</option>
                            <option value="sap_het" <?= ($stockFilter ?? '') === 'sap_het' ? 'selected' : '' ?>>Sắp hết hàng</option>
                            <option value="het_hang" <?= ($stockFilter ?? '') === 'het_hang' ? 'selected' : '' ?>>Hết hàng</option>
                        </select>
                    </div>
                    <div class="col-sm-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-moss rounded-pill px-3 flex-grow-1">Lọc</button>
                        <a href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=ingredients" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead>
                            <tr class="text-coffee-muted">
                                <th>Mã</th>
                                <th>Tên nguyên liệu</th>
                                <th class="text-end">Tồn kho</th>
                                <th class="text-end">Quy đổi gói / hộp / hũ / chai</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ingredients)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-coffee-muted py-4">Không có nguyên liệu nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ingredients as $nl): ?>
                                    <?php
                                    $ton = (float) ($nl['ton_kho_hien_tai'] ?? 0);
                                    $nguong = (float) ($nl['nguong_canh_bao'] ?? 0);
                                    $dvt = (string) ($nl['don_vi_tinh'] ?? '');
                                    $tenNl = (string) ($nl['ten_nguyen_lieu'] ?? '');
                                    if ($ton <= 0) {
                                        [$stBadge, $stLabel] = ['bg-danger', 'Hết hàng'];
                                    } elseif ($ton <= $nguong) {
                                        [$stBadge, $stLabel] = ['bg-warning text-dark', 'Sắp hết'];
                                    } else {
                                        [$stBadge, $stLabel] = ['bg-success', 'Đủ tồn'];
                                    }
                                    ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars((string) $nl['ma_nguyen_lieu']) ?></code></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($tenNl) ?></td>
                                        <td class="text-end fw-semibold text-nowrap">
                                            <?= format_qty_clean($ton) ?> <?= htmlspecialchars($dvt) ?>
                                        </td>
                                        <td class="text-end text-coffee-muted text-nowrap">
                                            <?= htmlspecialchars(format_pack_summary($ton, $nguong, $dvt, $tenNl)) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill <?= $stBadge ?>"><?= $stLabel ?></span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a
                                                href="<?= BASE_URL ?>/?c=admin&a=inventory&tab=ingredients&edit_nl=<?= (int) $nl['id'] ?>"
                                                class="btn btn-sm btn-outline-dark rounded-pill px-2"
                                            >
                                                Sửa
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=deleteIngredient" class="d-inline" onsubmit="return confirm('Xóa nguyên liệu này?');">
                                                <input type="hidden" name="id" value="<?= (int) $nl['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const unitSel = document.getElementById('nlUnitSelect');
        const nameInp = document.getElementById('nlTenInput');
        const hintEl = document.getElementById('nlConvertHint');
        if (!unitSel || !nameInp || !hintEl) return;

        function updateHint() {
            const u = unitSel.value;
            const n = (nameInp.value || '').toLowerCase();
            let text = '';
            if (u === 'hộp') {
                text = 'Tự quy đổi theo đơn vị ml: 1 hộp = 1.000 ml.';
            } else if (u === 'chai') {
                const isSyrup = n.includes('syrup') || n.includes('siro');
                text = isSyrup
                    ? 'Tự quy đổi theo đơn vị ml: 1 chai = 750 ml.'
                    : 'Tự quy đổi theo đơn vị ml: 1 chai = 500 ml.';
            } else if (u === 'hũ') {
                text = 'Tự quy đổi theo đơn vị g: 1 hũ = 500 g.';
            } else {
                text = 'Tự quy đổi theo đơn vị g: 1 gói = 500 g.';
            }
            hintEl.textContent = text;
        }

        unitSel.addEventListener('change', updateHint);
        nameInp.addEventListener('input', updateHint);
        updateHint();
    })();
    </script>

<?php elseif ($activeTab === 'stock'): ?>
    <div class="row g-4 align-items-start mb-4">
        <div class="col-xl-7">
            <div class="card card-coffee p-4">
                <h2 class="h5 mb-3">Tạo phiếu nhập kho</h2>
                <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=stockIn">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nhà cung cấp</label>
                            <input type="text" name="ten_nha_cung_cap" class="form-control form-control-sm border-coffee-soft" placeholder="VD: Kho nguyên liệu pha chế">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ghi chú phiếu nhập</label>
                            <input type="text" name="ghi_chu" class="form-control form-control-sm border-coffee-soft" placeholder="VD: Nhập hàng tuần">
                        </div>
                    </div>

                    <div class="row g-2 mb-1 small fw-semibold text-coffee-muted">
                        <div class="col-md-5">Nguyên liệu</div>
                        <div class="col-md-3">Số lượng (gói/hộp/hũ/chai)</div>
                        <div class="col-md-3">Đơn giá / gói,hộp... (VNĐ)</div>
                        <div class="col-md-1 text-end"></div>
                    </div>

                    <div id="stockInRows">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="row g-2 mb-2 align-items-center stock-in-row">
                                <div class="col-md-5">
                                    <select name="nguyen_lieu_id[]" class="form-select form-select-sm border-coffee-soft stock-nl-select">
                                        <option value="" data-pack="" data-note="">-- Chọn nguyên liệu --</option>
                                        <?php foreach ($ingredients as $nl): ?>
                                            <?php $spec = get_unit_spec((string) $nl['don_vi_tinh'], (string) $nl['ten_nguyen_lieu']); ?>
                                            <option
                                                value="<?= (int) $nl['id'] ?>"
                                                data-pack="<?= htmlspecialchars((string) $spec['unit_choice']) ?>"
                                                data-note="<?= htmlspecialchars((string) $spec['note']) ?>"
                                            >
                                                <?= htmlspecialchars((string) $nl['ten_nguyen_lieu']) ?> (<?= htmlspecialchars((string) $spec['unit_choice']) ?> · <?= htmlspecialchars((string) $spec['note']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="any" min="0" name="so_luong[]" class="form-control border-coffee-soft" placeholder="0">
                                        <span class="input-group-text border-coffee-soft bg-light stock-pack-badge">đv</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="1" min="0" name="don_gia_nhap[]" class="form-control form-control-sm border-coffee-soft" placeholder="0">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle px-2 remove-stock-row" title="Xóa dòng">&times;</button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                        <button type="button" id="addStockInRowBtn" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            + Thêm dòng nguyên liệu
                        </button>
                        <button type="submit" class="btn btn-sm btn-moss rounded-pill px-4">
                            Lưu phiếu nhập kho
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card card-coffee p-4">
                <h2 class="h5 mb-3">Xuất hủy / Hao hụt kho</h2>
                <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=stockOut">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nguyên liệu *</label>
                        <select id="stockOutNlSelect" name="nguyen_lieu_id" required class="form-select form-select-sm border-coffee-soft">
                            <option value="" data-base="g" data-pack="gói" data-note="">-- Chọn nguyên liệu --</option>
                            <?php foreach ($ingredients as $nl): ?>
                                <?php $spec = get_unit_spec((string) $nl['don_vi_tinh'], (string) $nl['ten_nguyen_lieu']); ?>
                                <option
                                    value="<?= (int) $nl['id'] ?>"
                                    data-base="<?= htmlspecialchars((string) $spec['base_unit']) ?>"
                                    data-pack="<?= htmlspecialchars((string) $spec['unit_choice']) ?>"
                                    data-note="<?= htmlspecialchars((string) $spec['note']) ?>"
                                >
                                    <?= htmlspecialchars((string) $nl['ten_nguyen_lieu']) ?> (Tồn: <?= format_qty_clean((float) $nl['ton_kho_hien_tai']) ?> <?= htmlspecialchars((string) $nl['don_vi_tinh']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số lượng xuất hủy *</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="any" min="0.001" name="so_luong" required class="form-control border-coffee-soft" placeholder="VD: 100 hoặc 1">
                            <select id="stockOutUnitSelect" name="don_vi_xuat_huy" class="form-select border-coffee-soft" style="max-width: 115px;">
                                <option id="optStockOutBase" value="base">g / ml</option>
                                <option id="optStockOutPack" value="pack">gói / hộp</option>
                            </select>
                        </div>
                        <div id="stockOutConvertHint" class="small text-coffee-muted mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lý do</label>
                        <input type="text" name="ly_do" class="form-control form-control-sm border-coffee-soft" placeholder="VD: Hao hụt, quá hạn">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-4 w-100">Xác nhận xuất hủy</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card card-coffee p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 mb-0">Lịch sử biến động kho</h2>
            <form method="GET" action="<?= BASE_URL ?>/" class="d-flex gap-2">
                <input type="hidden" name="c" value="admin">
                <input type="hidden" name="a" value="inventory">
                <input type="hidden" name="tab" value="stock">
                <select name="movement_type" class="form-select form-select-sm border-coffee-soft" onchange="this.form.submit()">
                    <option value="all" <?= ($movementType ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả biến động</option>
                    <option value="nhap_kho" <?= ($movementType ?? '') === 'nhap_kho' ? 'selected' : '' ?>>Nhập kho</option>
                    <option value="xuat_ban" <?= ($movementType ?? '') === 'xuat_ban' ? 'selected' : '' ?>>Xuất bán</option>
                    <option value="hoan_don" <?= ($movementType ?? '') === 'hoan_don' ? 'selected' : '' ?>>Hoàn đơn</option>
                    <option value="xuat_huy" <?= ($movementType ?? '') === 'xuat_huy' ? 'selected' : '' ?>>Xuất hủy</option>
                </select>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 small">
                <thead>
                    <tr class="text-coffee-muted">
                        <th>Thời gian</th>
                        <th>Nguyên liệu</th>
                        <th>Loại</th>
                        <th class="text-end">Thay đổi</th>
                        <th class="text-end">Tồn sau</th>
                        <th>Tham chiếu / Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-coffee-muted py-4">Chưa có biến động kho.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movements as $mv): ?>
                            <?php
                            $delta = (float) ($mv['so_luong_thay_doi'] ?? 0);
                            $mKey = (string) ($mv['loai_bien_dong'] ?? '');
                            [$mLabel, $mBadge] = $typeMeta[$mKey] ?? [$mKey, 'bg-secondary'];
                            ?>
                            <tr>
                                <td class="text-coffee-muted text-nowrap"><?= htmlspecialchars((string) $mv['ngay_tao']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars((string) $mv['ten_nguyen_lieu']) ?></td>
                                <td><span class="badge rounded-pill <?= $mBadge ?>"><?= htmlspecialchars($mLabel) ?></span></td>
                                <td class="text-end fw-semibold text-nowrap <?= $delta >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= ($delta >= 0 ? '+' : '') . format_qty_clean($delta) ?> <?= htmlspecialchars((string) $mv['don_vi_tinh']) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?= format_qty_clean((float) ($mv['ton_sau_bien_dong'] ?? 0)) ?> <?= htmlspecialchars((string) $mv['don_vi_tinh']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($mv['ma_tham_chieu'])): ?>
                                        <code><?= htmlspecialchars((string) $mv['ma_tham_chieu']) ?></code>
                                    <?php endif; ?>
                                    <?= htmlspecialchars((string) ($mv['ghi_chu'] ?? '')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    (function () {
        const container = document.getElementById('stockInRows');
        const addBtn = document.getElementById('addStockInRowBtn');
        if (container && addBtn) {
            function bindRow(row) {
                const sel = row.querySelector('.stock-nl-select');
                const badge = row.querySelector('.stock-pack-badge');
                const removeBtn = row.querySelector('.remove-stock-row');

                if (sel && badge) {
                    const syncBadge = () => {
                        const opt = sel.options[sel.selectedIndex];
                        const pack = opt ? (opt.getAttribute('data-pack') || '') : '';
                        badge.textContent = pack !== '' ? pack : 'đv';
                    };
                    sel.addEventListener('change', syncBadge);
                    syncBadge();
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        const rows = container.querySelectorAll('.stock-in-row');
                        if (rows.length > 1) {
                            row.remove();
                        } else {
                            row.querySelectorAll('select').forEach(s => { s.value = ''; });
                            row.querySelectorAll('input').forEach(i => { i.value = ''; });
                            if (badge) badge.textContent = 'đv';
                        }
                    });
                }
            }

            container.querySelectorAll('.stock-in-row').forEach(bindRow);

            addBtn.addEventListener('click', () => {
                const firstRow = container.querySelector('.stock-in-row');
                if (!firstRow) return;
                const clone = firstRow.cloneNode(true);
                clone.querySelectorAll('select').forEach(s => { s.value = ''; });
                clone.querySelectorAll('input').forEach(i => { i.value = ''; });
                const badge = clone.querySelector('.stock-pack-badge');
                if (badge) badge.textContent = 'đv';
                container.appendChild(clone);
                bindRow(clone);
            });
        }

        const outNlSel = document.getElementById('stockOutNlSelect');
        const outUnitSel = document.getElementById('stockOutUnitSelect');
        const optBase = document.getElementById('optStockOutBase');
        const optPack = document.getElementById('optStockOutPack');
        const outHint = document.getElementById('stockOutConvertHint');

        if (outNlSel && outUnitSel && optBase && optPack && outHint) {
            function syncStockOutUnits() {
                const opt = outNlSel.options[outNlSel.selectedIndex];
                const baseUnit = opt ? (opt.getAttribute('data-base') || 'g') : 'g';
                const packUnit = opt ? (opt.getAttribute('data-pack') || 'gói') : 'gói';
                const note = opt ? (opt.getAttribute('data-note') || '') : '';

                optBase.textContent = baseUnit;
                optPack.textContent = packUnit;

                if (outUnitSel.value === 'pack' && note !== '') {
                    outHint.textContent = 'Hủy theo ' + packUnit + ' (' + note + '), tự quy đổi sang ' + baseUnit + ' khi trừ kho.';
                } else {
                    outHint.textContent = 'Hủy trực tiếp theo ' + baseUnit + ' (quy đổi kiện: ' + (note || packUnit) + ').';
                }
            }

            outNlSel.addEventListener('change', syncStockOutUnits);
            outUnitSel.addEventListener('change', syncStockOutUnits);
            syncStockOutUnits();
        }
    })();
    </script>

<?php else: ?>
    <div class="row g-4 align-items-start">
        <div class="col-xl-5">
            <div class="card card-coffee p-4">
                <h2 class="h5 mb-3">Thiết lập công thức món</h2>
                <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=saveRecipe">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sản phẩm *</label>
                        <select name="san_pham_id" required class="form-select form-select-sm border-coffee-soft">
                            <option value="">-- Chọn món --</option>
                            <?php foreach ($products as $sp): ?>
                                <option value="<?= (int) $sp['id'] ?>" <?= ((int) ($recipeProductId ?? 0)) === (int) $sp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(format_product_option_label($sp)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-1 small fw-semibold text-coffee-muted">
                        <div class="col-md-5">Nguyên liệu *</div>
                        <div class="col-md-3">Định lượng *</div>
                        <div class="col-md-3">Ghi chú</div>
                        <div class="col-md-1 text-end"></div>
                    </div>

                    <div id="recipeRows">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="row g-2 mb-2 align-items-center recipe-row">
                                <div class="col-md-5">
                                    <select name="nguyen_lieu_id[]" class="form-select form-select-sm border-coffee-soft recipe-nl-select">
                                        <option value="" data-unit="g/ml">-- Chọn nguyên liệu --</option>
                                        <?php foreach ($ingredients as $nl): ?>
                                            <option
                                                value="<?= (int) $nl['id'] ?>"
                                                data-unit="<?= htmlspecialchars((string) $nl['don_vi_tinh']) ?>"
                                            >
                                                <?= htmlspecialchars((string) $nl['ten_nguyen_lieu']) ?> (<?= htmlspecialchars((string) $nl['don_vi_tinh']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="any" min="0.001" name="dinh_luong[]" class="form-control border-coffee-soft" placeholder="VD: 20">
                                        <span class="input-group-text border-coffee-soft bg-light recipe-unit-badge">đv</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="ghi_chu[]" class="form-control form-control-sm border-coffee-soft" placeholder="Tùy chọn">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle px-2 remove-recipe-row" title="Xóa dòng">&times;</button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                        <button type="button" id="addRecipeRowBtn" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                            + Thêm dòng nguyên liệu
                        </button>
                        <button type="submit" class="btn btn-sm btn-moss rounded-pill px-4">
                            Lưu công thức
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card card-coffee p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 mb-0">Danh sách công thức pha chế</h2>
                    <form method="GET" action="<?= BASE_URL ?>/" class="d-flex gap-2">
                        <input type="hidden" name="c" value="admin">
                        <input type="hidden" name="a" value="inventory">
                        <input type="hidden" name="tab" value="recipes">
                        <select name="recipe_product_id" class="form-select form-select-sm border-coffee-soft" onchange="this.form.submit()">
                            <option value="0">Tất cả món</option>
                            <?php foreach ($products as $sp): ?>
                                <option value="<?= (int) $sp['id'] ?>" <?= ((int) ($recipeProductId ?? 0)) === (int) $sp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(format_product_option_label($sp)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead>
                            <tr class="text-coffee-muted">
                                <th>Món</th>
                                <th>Nguyên liệu</th>
                                <th class="text-end">Định lượng / món</th>
                                <th class="text-end">Tồn kho</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recipes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-coffee-muted py-4">Chưa có công thức nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recipes as $rc): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars((string) $rc['ten_san_pham']) ?></td>
                                        <td><?= htmlspecialchars((string) $rc['ten_nguyen_lieu']) ?></td>
                                        <td class="text-end fw-semibold text-nowrap">
                                            <?= format_qty_clean((float) ($rc['dinh_luong'] ?? 0)) ?> <?= htmlspecialchars((string) $rc['don_vi_tinh']) ?>
                                        </td>
                                        <td class="text-end text-coffee-muted text-nowrap">
                                            <?= format_qty_clean((float) ($rc['ton_kho_hien_tai'] ?? 0)) ?> <?= htmlspecialchars((string) $rc['don_vi_tinh']) ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=deleteRecipe" class="d-inline" onsubmit="return confirm('Xóa nguyên liệu khỏi công thức?');">
                                                <input type="hidden" name="id" value="<?= (int) $rc['id'] ?>">
                                                <input type="hidden" name="recipe_product_id" value="<?= (int) ($recipeProductId ?? 0) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const container = document.getElementById('recipeRows');
        const addBtn = document.getElementById('addRecipeRowBtn');
        if (!container || !addBtn) return;

        function bindRecipeRow(row) {
            const sel = row.querySelector('.recipe-nl-select');
            const badge = row.querySelector('.recipe-unit-badge');
            const removeBtn = row.querySelector('.remove-recipe-row');

            if (sel && badge) {
                const syncUnit = () => {
                    const opt = sel.options[sel.selectedIndex];
                    const unit = opt && opt.value ? (opt.getAttribute('data-unit') || 'đv') : 'đv';
                    badge.textContent = unit;
                };
                sel.addEventListener('change', syncUnit);
                syncUnit();
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    const rows = container.querySelectorAll('.recipe-row');
                    if (rows.length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('select').forEach(s => { s.value = ''; });
                        row.querySelectorAll('input').forEach(i => { i.value = ''; });
                        if (badge) badge.textContent = 'đv';
                    }
                });
            }
        }

        container.querySelectorAll('.recipe-row').forEach(bindRecipeRow);

        addBtn.addEventListener('click', () => {
            const firstRow = container.querySelector('.recipe-row');
            if (!firstRow) return;
            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('select').forEach(s => { s.value = ''; });
            clone.querySelectorAll('input').forEach(i => { i.value = ''; });
            const badge = clone.querySelector('.recipe-unit-badge');
            if (badge) badge.textContent = 'đv';
            container.appendChild(clone);
            bindRecipeRow(clone);
        });
    })();
    </script>
<?php endif; ?>
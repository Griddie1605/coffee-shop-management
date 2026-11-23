<?php
/* Quản lý đồng bộ danh mục, sản phẩm và topping trên một màn hình catalog. */
?>
<div class="mb-4">
    <h1 class="h2 mb-1">Quản lý danh mục &amp; sản phẩm</h1>
    <p class="mb-0 text-coffee-muted">Thêm, sửa thông tin món, giá gốc, topping và trạng thái bán trên thực đơn</p>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success border-coffee-soft" role="alert">
        <?= htmlspecialchars($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger border-coffee-soft" role="alert">
        <?= htmlspecialchars($flashError) ?>
    </div>
<?php endif; ?>

<!-- HÀNG 1: THÊM SẢN PHẨM + DANH SÁCH SẢN PHẨM -->
<div class="row g-4 align-items-start mb-4">
    <div class="col-xl-5">
        <div class="card card-coffee p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">
                    <?= !empty($editProduct) ? 'Sửa sản phẩm #' . (int) $editProduct['id'] : 'Thêm sản phẩm mới' ?>
                </h2>
                <?php if (!empty($editProduct)): ?>
                    <a href="<?= BASE_URL ?>/?c=admin&a=catalog" class="small text-coffee-muted">Hủy sửa</a>
                <?php endif; ?>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=saveProduct" enctype="multipart/form-data">
                <?php if (!empty($editProduct)): ?>
                    <input type="hidden" name="id" value="<?= (int) $editProduct['id'] ?>">
                    <input type="hidden" name="hinh_anh_hien_tai" value="<?= htmlspecialchars((string) ($editProduct['hinh_anh'] ?? '')) ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tên sản phẩm *</label>
                        <input
                            type="text"
                            name="ten_san_pham"
                            required
                            maxlength="150"
                            class="form-control border-coffee-soft"
                            value="<?= htmlspecialchars((string) ($editProduct['ten_san_pham'] ?? '')) ?>"
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Danh mục *</label>
                        <select name="danh_muc_id" required class="form-select border-coffee-soft">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php $selectedCat = (int) ($editProduct['danh_muc_id'] ?? 0) === (int) $cat['id']; ?>
                                <option value="<?= (int) $cat['id'] ?>" <?= $selectedCat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $cat['ten_danh_muc']) ?>
                                    (<?= $cat['loai_mon'] === 'banh' ? 'Bánh' : 'Đồ uống' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Giá gốc (VNĐ) *</label>
                        <input
                            type="number"
                            name="gia_goc"
                            required
                            min="0"
                            step="500"
                            class="form-control border-coffee-soft"
                            value="<?= htmlspecialchars((string) ($editProduct['gia_goc'] ?? '0')) ?>"
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Hình ảnh (tùy chọn)</label>
                        <input
                            type="file"
                            name="hinh_anh_file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            class="form-control form-control-sm border-coffee-soft"
                        >
                        <?php if (!empty($editProduct['hinh_anh'])): ?>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <img
                                    src="<?= BASE_URL . '/' . ltrim((string) $editProduct['hinh_anh'], '/') ?>"
                                    alt="<?= htmlspecialchars((string) $editProduct['ten_san_pham']) ?>"
                                    class="rounded border border-coffee-soft"
                                    style="width: 38px; height: 38px; object-fit: cover;"
                                >
                                <span class="small text-coffee-muted text-truncate">
                                    <?= htmlspecialchars((string) $editProduct['hinh_anh']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Mô tả</label>
                        <textarea
                            name="mo_ta"
                            rows="2"
                            class="form-control border-coffee-soft"
                        ><?= htmlspecialchars((string) ($editProduct['mo_ta'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-between align-items-center pt-1">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="con_ban"
                                id="prodConBan"
                                value="1"
                                <?= (!isset($editProduct) || (int) ($editProduct['con_ban'] ?? 1) === 1) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label small" for="prodConBan">Đang mở bán</label>
                        </div>
                        <button type="submit" class="btn btn-moss rounded-pill px-4">
                            <?= !empty($editProduct) ? 'Lưu cập nhật' : 'Thêm món' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card card-coffee p-4 d-flex flex-column">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 mb-0">
                    Danh sách sản phẩm (<span id="productVisibleCount">0</span>)
                </h2>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button
                        type="button"
                        id="btnToggleStoppedProducts"
                        class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    >
                        Hiện món ngưng bán
                    </button>
                    <select id="filterProductCategory" class="form-select form-select-sm border-coffee-soft" style="width: auto; min-width: 170px;">
                        <option value="all">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>">
                                <?= htmlspecialchars((string) $cat['ten_danh_muc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="max-height: 315px; overflow-y: auto;">
                <table class="table align-middle mb-0 small">
                    <thead class="sticky-top bg-white">
                        <tr class="text-coffee-muted">
                            <th>Món</th>
                            <th>Danh mục</th>
                            <th class="text-end">Giá gốc</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <?php $isSelling = (int) ($prod['con_ban'] ?? 1) === 1; ?>
                            <tr
                                class="product-row"
                                data-category-id="<?= (int) $prod['danh_muc_id'] ?>"
                                data-active="<?= $isSelling ? '1' : '0' ?>"
                            >
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) $prod['ten_san_pham']) ?></div>
                                    <div class="text-coffee-muted"><?= htmlspecialchars((string) $prod['duong_dan']) ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) $prod['ten_danh_muc']) ?></td>
                                <td class="text-end fw-semibold"><?= format_vnd((float) $prod['gia_goc']) ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= $isSelling ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $isSelling ? 'Đang bán' : 'Ngưng bán' ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a
                                        href="<?= BASE_URL ?>/?c=admin&a=catalog&edit_prod=<?= (int) $prod['id'] ?>"
                                        class="btn btn-sm btn-outline-dark rounded-pill px-2"
                                    >
                                        Sửa
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=toggleProduct" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int) $prod['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                                            <?= $isSelling ? 'Tắt' : 'Bật' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="noFilteredProductsRow" style="display: none;">
                            <td colspan="5" class="text-center text-coffee-muted py-4">
                                Không có sản phẩm nào phù hợp.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- HÀNG 2: THÊM DANH MỤC + DANH SÁCH DANH MỤC -->
<div class="row g-4 align-items-start">
    <div class="col-xl-5">
        <div class="card card-coffee p-4" style="min-height: 305px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">
                    <?= !empty($editCategory) ? 'Sửa danh mục #' . (int) $editCategory['id'] : 'Thêm danh mục' ?>
                </h2>
                <?php if (!empty($editCategory)): ?>
                    <a href="<?= BASE_URL ?>/?c=admin&a=catalog" class="small text-coffee-muted">Hủy</a>
                <?php endif; ?>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=saveCategory">
                <?php if (!empty($editCategory)): ?>
                    <input type="hidden" name="id" value="<?= (int) $editCategory['id'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tên danh mục *</label>
                    <input
                        type="text"
                        name="ten_danh_muc"
                        required
                        maxlength="100"
                        class="form-control border-coffee-soft"
                        value="<?= htmlspecialchars((string) ($editCategory['ten_danh_muc'] ?? '')) ?>"
                    >
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Loại món *</label>
                        <select name="loai_mon" class="form-select border-coffee-soft">
                            <option value="do_uong" <?= (($editCategory['loai_mon'] ?? 'do_uong') === 'do_uong') ? 'selected' : '' ?>>
                                Đồ uống
                            </option>
                            <option value="banh" <?= (($editCategory['loai_mon'] ?? '') === 'banh') ? 'selected' : '' ?>>
                                Bánh
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold">Thứ tự hiển thị</label>
                        <input
                            type="number"
                            name="thu_tu_hien_thi"
                            class="form-control border-coffee-soft"
                            value="<?= htmlspecialchars((string) ($editCategory['thu_tu_hien_thi'] ?? '0')) ?>"
                        >
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="con_ban"
                            id="catConBan"
                            value="1"
                            <?= (!isset($editCategory) || (int) ($editCategory['con_ban'] ?? 1) === 1) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label small" for="catConBan">Đang bán</label>
                    </div>
                    <button type="submit" class="btn btn-moss rounded-pill px-4">
                        <?= !empty($editCategory) ? 'Lưu danh mục' : 'Thêm danh mục' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card card-coffee p-4 d-flex flex-column">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 mb-0">
                    Danh sách danh mục (<span id="categoryVisibleCount">0</span>)
                </h2>
                <button
                    type="button"
                    id="btnToggleStoppedCategories"
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                >
                    Hiện DM ngưng bán
                </button>
            </div>
            <div style="max-height: 215px; overflow-y: auto;">
                <table class="table align-middle mb-0 small">
                    <thead class="sticky-top bg-white">
                        <tr class="text-coffee-muted">
                            <th>Danh mục</th>
                            <th>Loại món</th>
                            <th class="text-center">Thứ tự</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <?php $catActive = (int) ($cat['con_ban'] ?? 1) === 1; ?>
                            <tr class="category-row" data-active="<?= $catActive ? '1' : '0' ?>">
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) $cat['ten_danh_muc']) ?></div>
                                    <div class="text-coffee-muted"><?= htmlspecialchars((string) $cat['duong_dan']) ?></div>
                                </td>
                                <td><?= $cat['loai_mon'] === 'banh' ? 'Bánh' : 'Đồ uống' ?></td>
                                <td class="text-center"><?= (int) $cat['thu_tu_hien_thi'] ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= $catActive ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $catActive ? 'Đang bán' : 'Ngưng bán' ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a
                                        href="<?= BASE_URL ?>/?c=admin&a=catalog&edit_cat=<?= (int) $cat['id'] ?>"
                                        class="btn btn-sm btn-outline-dark rounded-pill px-2"
                                    >
                                        Sửa
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=toggleCategory" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                                            <?= $catActive ? 'Tắt' : 'Bật' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="noFilteredCategoriesRow" style="display: none;">
                            <td colspan="5" class="text-center text-coffee-muted py-4">
                                Không có danh mục nào phù hợp.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<!-- HÀNG 3: QUẢN LÝ TOPPING -->
<div id="options-section" class="row g-4 align-items-start">
    <div class="col-xl-5">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3"><?= !empty($editTopping) ? 'Sửa topping' : 'Thêm topping mới' ?></h2>
            <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=saveTopping" class="row g-3">
                <?php if (!empty($editTopping)): ?>
                    <input type="hidden" name="id" value="<?= (int) $editTopping['id'] ?>">
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tên topping *</label>
                    <input
                        type="text"
                        name="ten_topping"
                        class="form-control form-control-sm border-coffee-soft"
                        placeholder="Ví dụ: Trân châu trắng, Kem cheese"
                        value="<?= htmlspecialchars((string) ($editTopping['ten_topping'] ?? '')) ?>"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Giá bán (VNĐ) *</label>
                    <input
                        type="number"
                        name="gia_ban"
                        min="0"
                        step="1000"
                        class="form-control form-control-sm border-coffee-soft"
                        value="<?= htmlspecialchars((string) ($editTopping['gia_ban'] ?? 0)) ?>"
                        required
                    >
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="con_ban"
                            id="toppingConBan"
                            value="1"
                            <?= (!isset($editTopping) || (int) ($editTopping['con_ban'] ?? 1) === 1) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label small" for="toppingConBan">Đang bán</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-moss rounded-pill px-4">
                        <?= !empty($editTopping) ? 'Lưu thay đổi' : 'Thêm topping' ?>
                    </button>
                    <?php if (!empty($editTopping)): ?>
                        <a href="<?= BASE_URL ?>/?c=admin&a=catalog#options-section" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Hủy sửa
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card card-coffee p-4">
            <h3 class="h5 mb-3">Danh sách topping (<?= count($toppings ?? []) ?>)</h3>
            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr class="text-coffee-muted">
                            <th>Tên topping</th>
                            <th class="text-end">Giá bán</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($toppings)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-coffee-muted py-3">Chưa có topping nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($toppings as $t): ?>
                                <?php $on = (int) ($t['con_ban'] ?? 1) === 1; ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) $t['ten_topping']) ?></td>
                                    <td class="text-end"><?= format_vnd((float) $t['gia_ban']) ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill <?= $on ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $on ? 'Đang bán' : 'Tắt' ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="<?= BASE_URL ?>/?c=admin&a=catalog&edit_topping=<?= (int) $t['id'] ?>#options-section" class="btn btn-sm btn-outline-dark rounded-pill px-2">
                                            Sửa
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=toggleTopping" class="d-inline">
                                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                                                <?= $on ? 'Tắt' : 'Bật' ?>
                                            </button>
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
document.addEventListener('DOMContentLoaded', function () {
    const selectFilter = document.getElementById('filterProductCategory');
    const btnStoppedProd = document.getElementById('btnToggleStoppedProducts');
    const prodRows = document.querySelectorAll('tr.product-row');
    const emptyProdRow = document.getElementById('noFilteredProductsRow');
    const prodCountEl = document.getElementById('productVisibleCount');

    const btnStoppedCat = document.getElementById('btnToggleStoppedCategories');
    const catRows = document.querySelectorAll('tr.category-row');
    const emptyCatRow = document.getElementById('noFilteredCategoriesRow');
    const catCountEl = document.getElementById('categoryVisibleCount');

    let showStoppedProducts = false;
    let showStoppedCategories = false;

    function updateProductsView() {
        const selectedCat = selectFilter ? selectFilter.value : 'all';
        let visibleCount = 0;

        prodRows.forEach(function (row) {
            const catId = row.getAttribute('data-category-id');
            const isActive = row.getAttribute('data-active') === '1';
            const matchCat = (selectedCat === 'all' || catId === selectedCat);
            const matchStatus = (isActive || showStoppedProducts);
            const show = matchCat && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (prodCountEl) prodCountEl.textContent = String(visibleCount);
        if (emptyProdRow) emptyProdRow.style.display = visibleCount === 0 ? '' : 'none';
        if (btnStoppedProd) {
            btnStoppedProd.textContent = showStoppedProducts ? 'Ẩn món ngưng bán' : 'Hiện món ngưng bán';
        }
    }

    function updateCategoriesView() {
        let visibleCount = 0;

        catRows.forEach(function (row) {
            const isActive = row.getAttribute('data-active') === '1';
            const show = (isActive || showStoppedCategories);
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (catCountEl) catCountEl.textContent = String(visibleCount);
        if (emptyCatRow) emptyCatRow.style.display = visibleCount === 0 ? '' : 'none';
        if (btnStoppedCat) {
            btnStoppedCat.textContent = showStoppedCategories ? 'Ẩn DM ngưng bán' : 'Hiện DM ngưng bán';
        }
    }

    if (selectFilter) selectFilter.addEventListener('change', updateProductsView);
    if (btnStoppedProd) {
        btnStoppedProd.addEventListener('click', function () {
            showStoppedProducts = !showStoppedProducts;
            updateProductsView();
        });
    }
    if (btnStoppedCat) {
        btnStoppedCat.addEventListener('click', function () {
            showStoppedCategories = !showStoppedCategories;
            updateCategoriesView();
        });
    }

    updateProductsView();
    updateCategoriesView();
});
</script>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-coffee-soft" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
        <a href="<?= BASE_URL ?>/?c=cart&a=index" class="alert-link ms-2">Xem giỏ hàng</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Thực đơn quán</h1>
        <p class="mb-0 text-coffee-muted">Lọc theo danh mục, tìm món và đặt mua trực tiếp ngay trên thực đơn</p>
    </div>
    <span class="badge badge-moss rounded-pill px-3 py-2">
        <?= count($products) ?> món hiển thị
    </span>
</div>

<!-- Thanh tìm kiếm -->
<form method="GET" action="<?= BASE_URL ?>/" class="row g-2 mb-4">
    <input type="hidden" name="c" value="home">
    <input type="hidden" name="a" value="menu">
    <?php if ($selectedCategory !== null && $selectedCategory > 0): ?>
        <input type="hidden" name="category" value="<?= (int) $selectedCategory ?>">
    <?php endif; ?>
    <div class="col-sm-9 col-md-10">
        <input
            type="text"
            name="q"
            class="form-control border-coffee-soft"
            placeholder="Tìm tên món (ví dụ: cà phê, matcha, bánh...)"
            value="<?= htmlspecialchars($keyword) ?>"
        >
    </div>
    <div class="col-sm-3 col-md-2 d-grid">
        <button type="submit" class="btn btn-moss">Tìm kiếm</button>
    </div>
</form>

<!-- Bộ lọc danh mục -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a
        href="<?= BASE_URL ?>/?c=home&a=menu<?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>"
        class="btn btn-sm rounded-pill px-3 <?= ($selectedCategory === null || $selectedCategory <= 0) ? 'btn-moss' : 'btn-outline-dark' ?>"
    >
        Tất cả
    </a>
    <?php foreach ($categories as $cat): ?>
        <?php $isActive = ((int) $selectedCategory === (int) $cat['id']); ?>
        <a
            href="<?= BASE_URL ?>/?c=home&a=menu&category=<?= (int) $cat['id'] ?><?= $keyword !== '' ? '&q=' . urlencode($keyword) : '' ?>"
            class="btn btn-sm rounded-pill px-3 <?= $isActive ? 'btn-moss' : 'btn-outline-dark' ?>"
        >
            <?= htmlspecialchars($cat['ten_danh_muc']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Danh sách sản phẩm -->
<?php if (empty($products)): ?>
    <div class="card card-coffee p-5 text-center">
        <p class="mb-2 fw-semibold">Không tìm thấy món phù hợp với bộ lọc hiện tại.</p>
        <div>
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-sm btn-moss rounded-pill px-3">
                Xem toàn bộ thực đơn
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $item): ?>
            <div class="col-md-4">
                <div class="card card-coffee h-100">
                    <div class="card-body p-4">
                        <span class="badge badge-moss rounded-pill mb-2">
                            <?= htmlspecialchars($item['ten_danh_muc']) ?>
                        </span>
                        <h2 class="h5 card-title mt-1 mb-2"><?= htmlspecialchars($item['ten_san_pham']) ?></h2>
                        <p class="card-text small mb-0 text-coffee-muted">
                            <?= htmlspecialchars($item['mo_ta'] ?? '') ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-coffee-soft px-4 py-3 d-flex justify-content-between align-items-center gap-2">
                        <span class="price-moss fs-6"><?= format_vnd((float) $item['gia_goc']) ?></span>
                        <button
                            type="button"
                            class="btn btn-sm btn-moss rounded-pill px-3 btn-open-option-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#itemOptionModal"
                            data-id="<?= (int) $item['id'] ?>"
                            data-name="<?= htmlspecialchars($item['ten_san_pham'], ENT_QUOTES) ?>"
                            data-base-price="<?= (float) $item['gia_goc'] ?>"
                        >
                            Thêm món
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal chọn Topping, Ghi chú và Số lượng -->
<div class="modal fade" id="itemOptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="<?= BASE_URL ?>/?c=cart&a=add" class="modal-content border-coffee-soft">
            <div class="modal-header">
                <h5 class="modal-title" id="optionModalTitle">Tùy chọn món</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="modalProductId">

                <?php if (!empty($toppings)): ?>
                    <div class="mb-3">
                        <div class="small fw-semibold mb-1">Topping thêm</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($toppings as $tp): ?>
                                <label class="form-check form-check-inline small mb-0">
                                    <input
                                        type="checkbox"
                                        name="topping_ids[]"
                                        value="<?= (int) $tp['id'] ?>"
                                        class="form-check-input modal-topping-cb"
                                    >
                                    <span class="form-check-label">
                                        <?= htmlspecialchars($tp['ten_topping']) ?> (+<?= format_vnd((float) $tp['gia_ban']) ?>)
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Ghi chú thêm</label>
                    <input
                        type="text"
                        name="note"
                        id="modalNoteInput"
                        maxlength="200"
                        class="form-control form-control-sm border-coffee-soft"
                        placeholder="Ví dụ: ít đá, ít ngọt, lấy muỗng..."
                    >
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold">Số lượng</span>
                    <input
                        type="number"
                        name="qty"
                        id="modalQtyInput"
                        value="1"
                        min="1"
                        max="50"
                        class="form-control form-control-sm border-coffee-soft text-center"
                        style="width: 72px;"
                    >
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="price-moss fs-6" id="modalEstimatedPrice">0 đ</span>
                <button type="submit" class="btn btn-moss rounded-pill px-4">Xác nhận thêm</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('itemOptionModal');
    if (!modalEl) return;

    const titleEl = document.getElementById('optionModalTitle');
    const productIdEl = document.getElementById('modalProductId');
    const noteInputEl = document.getElementById('modalNoteInput');
    const qtyInputEl = document.getElementById('modalQtyInput');
    const priceEl = document.getElementById('modalEstimatedPrice');
    const formatter = new Intl.NumberFormat('vi-VN');

    function updateModalPrice() {
        const basePrice = parseFloat(modalEl.dataset.basePrice || '0') || 0;

        let toppingExtra = 0;
        modalEl.querySelectorAll('.modal-topping-cb:checked').forEach(cb => {
            const labelText = cb.closest('label')?.textContent || '';
            const match = labelText.match(/\(\+([\d.,]+)\s*đ\)/);
            if (match) {
                toppingExtra += parseInt(match[1].replace(/[^\d]/g, ''), 10) || 0;
            }
        });

        const qty = Math.max(1, parseInt(qtyInputEl.value, 10) || 1);
        const total = (basePrice + toppingExtra) * qty;
        priceEl.textContent = formatter.format(total) + ' đ';
    }

    document.querySelectorAll('.btn-open-option-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const basePrice = parseFloat(this.dataset.basePrice || '0') || 0;
            modalEl.dataset.basePrice = String(basePrice);
            titleEl.textContent = this.dataset.name || 'Tùy chọn món';
            productIdEl.value = this.dataset.id || '';
            noteInputEl.value = '';
            qtyInputEl.value = 1;
            modalEl.querySelectorAll('.modal-topping-cb').forEach(cb => { cb.checked = false; });

            updateModalPrice();
        });
    });

    qtyInputEl.addEventListener('input', updateModalPrice);
    modalEl.querySelectorAll('.modal-topping-cb').forEach(cb => cb.addEventListener('change', updateModalPrice));
});
</script>
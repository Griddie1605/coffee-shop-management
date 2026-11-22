<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Giỏ hàng của bạn</h1>
        <p class="mb-0 text-coffee-muted">Kiểm tra các món đã chọn trước khi đặt hàng</p>
    </div>
    <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-sm btn-outline-dark rounded-pill px-3">
        Tiếp tục chọn món
    </a>
</div>

<?php if (!empty($_SESSION['cart_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-coffee-soft" role="alert">
        <?= htmlspecialchars($_SESSION['cart_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="card card-coffee p-5 text-center">
        <p class="mb-3 fw-semibold">Giỏ hàng hiện đang trống.</p>
        <div>
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-moss rounded-pill px-4">
                Khám phá thực đơn
            </a>
        </div>
    </div>
<?php else: ?>
    <form method="POST" action="<?= BASE_URL ?>/?c=cart&a=update" class="card card-coffee p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Món</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-center" style="width: 120px;">Số lượng</th>
                        <th class="text-end text-nowrap">Thành tiền</th>
                        <th class="text-end text-nowrap" style="width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $key => $item): ?>
                        <?php
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['qty'] ?? 1);
                        $lineTotal = $price * $qty;
                        $pid = (int) ($item['product_id'] ?? 0);
                        $itemSizes = $sizesByProduct[$pid] ?? [];
                        $itemToppings = array_map('intval', (array) ($item['topping_ids'] ?? []));
                        $basePrice = $price;
                        foreach ($itemSizes as $sz) {
                            if ((int) $sz['id'] === (int) ($item['size_id'] ?? 0)) {
                                $basePrice -= (float) $sz['gia_cong_them'];
                                break;
                            }
                        }
                        if (!empty($toppings) && !empty($itemToppings)) {
                            foreach ($toppings as $tp) {
                                if (in_array((int) $tp['id'], $itemToppings, true)) {
                                    $basePrice -= (float) $tp['gia_ban'];
                                }
                            }
                        }
                        $basePrice = max(0, $basePrice);
                        ?>
                        <tr>
                            <td class="fw-semibold">
                                <div><?= htmlspecialchars((string) ($item['name'] ?? '')) ?></div>
                                <?php if (!empty($item['note'])): ?>
                                    <div class="small fw-normal text-coffee-muted mt-1">
                                        Ghi chú: <?= htmlspecialchars((string) $item['note']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap"><?= format_vnd($price) ?></td>
                            <td class="text-center">
                                <input
                                    type="number"
                                    name="qty[<?= htmlspecialchars((string) $key) ?>]"
                                    value="<?= $qty ?>"
                                    min="0"
                                    max="50"
                                    class="form-control form-control-sm border-coffee-soft text-center mx-auto"
                                    style="width: 72px;"
                                >
                            </td>
                            <td class="text-end text-nowrap price-moss"><?= format_vnd($lineTotal) ?></td>
                            <td class="text-end text-nowrap">
                                <!-- Mở modal sửa size, topping, ghi chú và số lượng của dòng này -->
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-dark rounded-pill px-2 me-1 btn-edit-cart-item"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCartItemModal"
                                    data-old-key="<?= htmlspecialchars((string) $key, ENT_QUOTES) ?>"
                                    data-name="<?= htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES) ?>"
                                    data-size-id="<?= (int) ($item['size_id'] ?? 0) ?>"
                                    data-topping-ids="<?= htmlspecialchars(json_encode($itemToppings), ENT_QUOTES) ?>"
                                    data-note="<?= htmlspecialchars((string) ($item['note'] ?? ''), ENT_QUOTES) ?>"
                                    data-qty="<?= $qty ?>"
                                    data-base-price="<?= $basePrice ?>"
                                    data-sizes="<?= htmlspecialchars(json_encode($itemSizes, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>"
                                >
                                    Sửa
                                </button>
                                <a
                                    href="<?= BASE_URL ?>/?c=cart&a=remove&key=<?= urlencode((string) $key) ?>"
                                    class="btn btn-sm btn-outline-danger rounded-pill px-2"
                                >
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <hr class="border-coffee-soft my-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <button type="submit" class="btn btn-outline-dark rounded-pill px-4">
                Cập nhật số lượng
            </button>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="fs-5">
                    <span>Tổng tạm tính:</span>
                    <strong class="price-moss ms-2"><?= format_vnd($totalAmount) ?></strong>
                </div>
                <button type="button" class="btn btn-moss rounded-pill px-4" disabled>
                    Thanh toán (Module 4)
                </button>
            </div>
        </div>
    </form>

    <!-- Modal sửa tùy chọn món trong giỏ -->
    <div class="modal fade" id="editCartItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="<?= BASE_URL ?>/?c=cart&a=edit" class="modal-content border-coffee-soft">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Sửa món</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="old_key" id="editOldKey">

                    <div class="mb-3" id="editSizeWrap">
                        <label class="form-label small fw-semibold">Chọn Size</label>
                        <select name="size_id" id="editSizeSelect" class="form-select form-select-sm border-coffee-soft"></select>
                    </div>

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
                                            class="form-check-input edit-topping-cb"
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
                            id="editNoteInput"
                            maxlength="200"
                            class="form-control form-control-sm border-coffee-soft"
                            placeholder="Ví dụ: ít đá, ít ngọt..."
                        >
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small fw-semibold">Số lượng</span>
                        <input
                            type="number"
                            name="qty"
                            id="editQtyInput"
                            value="1"
                            min="1"
                            max="50"
                            class="form-control form-control-sm border-coffee-soft text-center"
                            style="width: 72px;"
                        >
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="price-moss fs-6" id="editEstimatedPrice">0 đ</span>
                    <button type="submit" class="btn btn-moss rounded-pill px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const cartForm = document.querySelector('form[action*="c=cart&a=update"]');
        if (cartForm) {
            const totalEl = cartForm.querySelector('.fs-5 .price-moss');
            const formatter = new Intl.NumberFormat('vi-VN');

            function recalculateCart() {
                let grandTotal = 0;
                cartForm.querySelectorAll('tbody tr').forEach(function (row) {
                    const qtyInput = row.querySelector('input[type="number"][name^="qty["]');
                    const lineTotalEl = row.querySelector('td.text-end.price-moss');
                    if (!qtyInput || !lineTotalEl) return;

                    const priceText = row.querySelectorAll('td')[1]?.textContent || '0';
                    const unitPrice = parseInt(priceText.replace(/[^\d]/g, ''), 10) || 0;
                    const qty = Math.max(0, parseInt(qtyInput.value, 10) || 0);
                    const lineTotal = unitPrice * qty;

                    lineTotalEl.textContent = formatter.format(lineTotal) + ' đ';
                    grandTotal += lineTotal;
                });
                if (totalEl) {
                    totalEl.textContent = formatter.format(grandTotal) + ' đ';
                }
            }

            cartForm.querySelectorAll('input[type="number"][name^="qty["]').forEach(function (input) {
                input.addEventListener('input', recalculateCart);
                input.addEventListener('change', recalculateCart);
            });
        }

        const editModalEl = document.getElementById('editCartItemModal');
        if (!editModalEl) return;

        const oldKeyEl = document.getElementById('editOldKey');
        const titleEl = document.getElementById('editModalTitle');
        const sizeWrapEl = document.getElementById('editSizeWrap');
        const sizeSelectEl = document.getElementById('editSizeSelect');
        const noteInputEl = document.getElementById('editNoteInput');
        const qtyInputEl = document.getElementById('editQtyInput');
        const priceEl = document.getElementById('editEstimatedPrice');
        const formatter = new Intl.NumberFormat('vi-VN');
        let currentSizes = [];

        function updateEditPrice() {
            const basePrice = parseFloat(editModalEl.dataset.basePrice || '0') || 0;
            const selectedSize = currentSizes.find(s => String(s.id) === String(sizeSelectEl.value));
            const sizeExtra = selectedSize ? (parseFloat(selectedSize.gia_cong_them) || 0) : 0;

            let toppingExtra = 0;
            editModalEl.querySelectorAll('.edit-topping-cb:checked').forEach(cb => {
                const labelText = cb.closest('label')?.textContent || '';
                const match = labelText.match(/\(\+([\d.,]+)\s*đ\)/);
                if (match) {
                    toppingExtra += parseInt(match[1].replace(/[^\d]/g, ''), 10) || 0;
                }
            });

            const qty = Math.max(1, parseInt(qtyInputEl.value, 10) || 1);
            priceEl.textContent = formatter.format((basePrice + sizeExtra + toppingExtra) * qty) + ' đ';
        }

        document.querySelectorAll('.btn-edit-cart-item').forEach(btn => {
            btn.addEventListener('click', function () {
                oldKeyEl.value = this.dataset.oldKey || '';
                titleEl.textContent = 'Sửa: ' + (this.dataset.name || 'Món');
                noteInputEl.value = this.dataset.note || '';
                qtyInputEl.value = this.dataset.qty || 1;
                editModalEl.dataset.basePrice = this.dataset.basePrice || '0';

                let selectedToppings = [];
                try { selectedToppings = JSON.parse(this.dataset.toppingIds || '[]'); } catch (e) {}
                editModalEl.querySelectorAll('.edit-topping-cb').forEach(cb => {
                    cb.checked = selectedToppings.includes(Number(cb.value));
                });

                try { currentSizes = JSON.parse(this.dataset.sizes || '[]'); } catch (e) { currentSizes = []; }
                sizeSelectEl.innerHTML = '';
                if (Array.isArray(currentSizes) && currentSizes.length > 0) {
                    sizeWrapEl.classList.remove('d-none');
                    currentSizes.forEach(sz => {
                        const opt = document.createElement('option');
                        const extra = parseFloat(sz.gia_cong_them) || 0;
                        opt.value = sz.id;
                        opt.textContent = sz.ten_size + (extra > 0 ? ' (+' + formatter.format(extra) + ' đ)' : '');
                        if (String(sz.id) === String(this.dataset.sizeId || '')) opt.selected = true;
                        sizeSelectEl.appendChild(opt);
                    });
                } else {
                    sizeWrapEl.classList.add('d-none');
                }

                updateEditPrice();
            });
        });

        sizeSelectEl.addEventListener('change', updateEditPrice);
        qtyInputEl.addEventListener('input', updateEditPrice);
        editModalEl.querySelectorAll('.edit-topping-cb').forEach(cb => cb.addEventListener('change', updateEditPrice));
    });
    </script>
<?php endif; ?>

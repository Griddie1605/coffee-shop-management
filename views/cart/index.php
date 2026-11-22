<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Giỏ hàng của bạn</h1>
        <p class="mb-0 text-coffee-muted">Kiểm tra các món đã chọn trước khi đặt hàng</p>
    </div>
    <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-sm btn-outline-dark rounded-pill px-3">
        Tiếp tục chọn món
    </a>
</div>

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
    <div class="card card-coffee p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Món</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <?php
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['qty'] ?? 1);
                        $lineTotal = $price * $qty;
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars((string) ($item['name'] ?? '')) ?></td>
                            <td class="text-end"><?= format_vnd($price) ?></td>
                            <td class="text-center"><?= $qty ?></td>
                            <td class="text-end price-moss"><?= format_vnd($lineTotal) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <hr class="border-coffee-soft my-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="fs-5">
                <span>Tổng tạm tính:</span>
                <strong class="price-moss ms-2"><?= format_vnd($totalAmount) ?></strong>
            </div>
            <button type="button" class="btn btn-moss rounded-pill px-4" disabled>
                Thanh toán (Module 4)
            </button>
        </div>
    </div>
<?php endif; ?>

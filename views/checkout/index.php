<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Thanh toán đơn hàng</h1>
        <p class="mb-0 text-coffee-muted">Điền thông tin nhận hàng và xác nhận đơn tại CF Song Thư</p>
    </div>
    <a href="<?= BASE_URL ?>/?c=cart&a=index" class="btn btn-sm btn-outline-dark rounded-pill px-3">
        Quay lại giỏ hàng
    </a>
</div>

<?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger border-coffee-soft" role="alert">
        <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/?c=checkout&a=placeOrder" class="row g-4">
    <div class="col-lg-7">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Thông tin nhận hàng</h2>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Họ và tên người nhận *</label>
                    <input
                        type="text"
                        name="ten_nguoi_nhan"
                        required
                        maxlength="100"
                        class="form-control border-coffee-soft"
                        value="<?= htmlspecialchars((string) ($oldInput['ten_nguoi_nhan'] ?? '')) ?>"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Số điện thoại *</label>
                    <input
                        type="text"
                        name="sdt_nguoi_nhan"
                        required
                        maxlength="20"
                        class="form-control border-coffee-soft"
                        value="<?= htmlspecialchars((string) ($oldInput['sdt_nguoi_nhan'] ?? '')) ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold d-block">Hình thức nhận hàng</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="form-check">
                            <input
                                type="radio"
                                name="hinh_thuc_nhan"
                                value="giao_tan_noi"
                                class="form-check-input checkout-delivery-radio"
                                <?= ($oldInput['hinh_thuc_nhan'] ?? 'giao_tan_noi') === 'giao_tan_noi' ? 'checked' : '' ?>
                            >
                            <span class="form-check-label">Giao tận nơi</span>
                        </label>
                        <label class="form-check">
                            <input
                                type="radio"
                                name="hinh_thuc_nhan"
                                value="den_quan_lay"
                                class="form-check-input checkout-delivery-radio"
                                <?= ($oldInput['hinh_thuc_nhan'] ?? '') === 'den_quan_lay' ? 'checked' : '' ?>
                            >
                            <span class="form-check-label">Đến quán lấy</span>
                        </label>
                    </div>
                </div>

                <div class="col-12" id="deliveryAddressGroup">
                    <label class="form-label small fw-semibold">Địa chỉ giao hàng *</label>
                    <textarea
                        name="dia_chi_giao"
                        id="deliveryAddressInput"
                        rows="2"
                        class="form-control border-coffee-soft"
                        placeholder="Số nhà, đường, phường/xã..."
                    ><?= htmlspecialchars((string) ($oldInput['dia_chi_giao'] ?? '')) ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold d-block">Phương thức thanh toán</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="form-check">
                            <input
                                type="radio"
                                name="phuong_thuc_thanh_toan"
                                value="tien_mat"
                                class="form-check-input"
                                <?= ($oldInput['phuong_thuc_thanh_toan'] ?? 'tien_mat') === 'tien_mat' ? 'checked' : '' ?>
                            >
                            <span class="form-check-label">Tiền mặt</span>
                        </label>
                        <label class="form-check">
                            <input
                                type="radio"
                                name="phuong_thuc_thanh_toan"
                                value="chuyen_khoan"
                                class="form-check-input"
                                <?= ($oldInput['phuong_thuc_thanh_toan'] ?? '') === 'chuyen_khoan' ? 'checked' : '' ?>
                            >
                            <span class="form-check-label">Chuyển khoản</span>
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Ghi chú đơn hàng</label>
                    <textarea
                        name="ghi_chu"
                        rows="2"
                        maxlength="500"
                        class="form-control border-coffee-soft"
                        placeholder="Ví dụ: gọi trước khi giao, ít đá chung..."
                    ><?= htmlspecialchars((string) ($oldInput['ghi_chu'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Tóm tắt đơn hàng</h2>

            <div class="vstack gap-3 mb-3">
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    // Trang thanh toán dùng dữ liệu giỏ hàng/session làm nguồn tổng tiền và chỉ hiển thị dữ liệu đã được validate ở controller.
                    $qty = (int) ($item['qty'] ?? 1);
                    $lineTotal = ((float) ($item['price'] ?? 0)) * $qty;
                    ?>
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold small">
                                <?= htmlspecialchars((string) ($item['name'] ?? '')) ?>
                                <span class="text-coffee-muted">x<?= $qty ?></span>
                            </div>
                            <?php if (!empty($item['note'])): ?>
                                <div class="small text-coffee-muted">
                                    Ghi chú: <?= htmlspecialchars((string) $item['note']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="small text-nowrap price-moss"><?= format_vnd($lineTotal) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="border-coffee-soft my-3">

            <div class="d-flex justify-content-between small mb-2">
                <span>Tạm tính</span>
                <span><?= format_vnd((float) $subtotal) ?></span>
            </div>

            <?php if (($discountAmount ?? 0) > 0): ?>
                <div class="d-flex justify-content-between small mb-2 text-success">
                    <span>Mã giảm giá (<?= htmlspecialchars((string) ($appliedVoucher['ma_code'] ?? '')) ?>)</span>
                    <span>-<?= format_vnd((float) $discountAmount) ?></span>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between fs-5 fw-bold mt-2">
                <span>Tổng thanh toán</span>
                <span class="price-moss"><?= format_vnd((float) $finalAmount) ?></span>
            </div>

            <button type="submit" class="btn btn-moss rounded-pill w-100 mt-4 py-2">
                Xác nhận đặt hàng
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addressGroup = document.getElementById('deliveryAddressGroup');
    const addressInput = document.getElementById('deliveryAddressInput');
    const radios = document.querySelectorAll('.checkout-delivery-radio');

    function syncDeliveryFields() {
        const selected = document.querySelector('.checkout-delivery-radio:checked')?.value || 'giao_tan_noi';
        const isDelivery = selected === 'giao_tan_noi';
        if (addressGroup) addressGroup.classList.toggle('d-none', !isDelivery);
        if (addressInput) addressInput.required = isDelivery;
    }

    radios.forEach(r => r.addEventListener('change', syncDeliveryFields));
    syncDeliveryFields();
});
</script>
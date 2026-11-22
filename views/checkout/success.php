<div class="card card-coffee p-5 text-center mx-auto" style="max-width: 560px;">
    <h1 class="h3 mb-2">Đặt hàng thành công</h1>
    <p class="text-coffee-muted mb-3">
        Mã đơn hàng của bạn là <strong class="text-dark"><?= htmlspecialchars($orderCode) ?></strong>.
        Đơn đang ở trạng thái <strong>Chờ xác nhận</strong>.
    </p>
    <div class="d-flex justify-content-center gap-2 mt-2">
        <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-moss rounded-pill px-4">
            Tiếp tục đặt món
        </a>
        <a href="<?= BASE_URL ?>/" class="btn btn-outline-dark rounded-pill px-4">
            Về trang chủ
        </a>
    </div>
</div>
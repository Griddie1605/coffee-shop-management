<div class="card card-coffee p-4 p-md-5 mx-auto" style="max-width: 520px;">
    <h1 class="h3 mb-1">Đăng ký tài khoản</h1>
    <p class="text-coffee-muted small mb-4">Tạo tài khoản khách hàng 47th cafe</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-coffee-soft small" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?c=auth&a=register">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Họ và tên *</label>
            <input
                type="text"
                name="ho_ten"
                required
                maxlength="100"
                class="form-control border-coffee-soft"
                value="<?= htmlspecialchars($old['ho_ten']) ?>"
            >
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Email *</label>
            <input
                type="email"
                name="email"
                required
                maxlength="120"
                class="form-control border-coffee-soft"
                value="<?= htmlspecialchars($old['email']) ?>"
            >
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Số điện thoại</label>
            <input
                type="text"
                name="so_dien_thoai"
                maxlength="20"
                class="form-control border-coffee-soft"
                value="<?= htmlspecialchars($old['so_dien_thoai']) ?>"
            >
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Địa chỉ mặc định</label>
            <textarea
                name="dia_chi"
                rows="2"
                class="form-control border-coffee-soft"
            ><?= htmlspecialchars($old['dia_chi']) ?></textarea>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Mật khẩu *</label>
                <input
                    type="password"
                    name="password"
                    required
                    minlength="6"
                    class="form-control border-coffee-soft"
                >
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Nhập lại mật khẩu *</label>
                <input
                    type="password"
                    name="confirm_password"
                    required
                    minlength="6"
                    class="form-control border-coffee-soft"
                >
            </div>
        </div>

        <button type="submit" class="btn btn-moss rounded-pill w-100 py-2">
            Đăng ký
        </button>
    </form>

    <div class="text-center small text-coffee-muted mt-4">
        Đã có tài khoản?
        <a href="<?= BASE_URL ?>/?c=auth&a=login" class="fw-semibold text-dark">Đăng nhập</a>
    </div>
</div>
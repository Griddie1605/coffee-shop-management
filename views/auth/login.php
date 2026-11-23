<div class="card card-coffee p-4 p-md-5 mx-auto" style="max-width: 460px;">
    <h1 class="h3 mb-1">Đăng nhập</h1>
    <p class="text-coffee-muted small mb-4">Đăng nhập để đặt hàng và theo dõi đơn tại CF Song Thư</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-coffee-soft small" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/?c=auth&a=login<?= !empty($_GET['redirect']) ? '&redirect=' . urlencode((string) $_GET['redirect']) : '' ?>">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Email</label>
            <input
                type="email"
                name="email"
                required
                class="form-control border-coffee-soft"
                value="<?= htmlspecialchars($email) ?>"
            >
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold">Mật khẩu</label>
            <input
                type="password"
                name="password"
                required
                class="form-control border-coffee-soft"
            >
        </div>

        <button type="submit" class="btn btn-moss rounded-pill w-100 py-2">
            Đăng nhập
        </button>
    </form>

    <div class="text-center small text-coffee-muted mt-4">
        Chưa có tài khoản?
        <a href="<?= BASE_URL ?>/?c=auth&a=register" class="fw-semibold text-dark">Đăng ký ngay</a>
    </div>
</div>
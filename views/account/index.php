<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Tài khoản của tôi</h1>
        <p class="mb-0 text-coffee-muted">Cập nhật số điện thoại, địa chỉ giao hàng mặc định và mật khẩu</p>
    </div>
    <a href="<?= BASE_URL ?>/?c=order&a=index" class="btn btn-sm btn-outline-dark rounded-pill px-3">
        Đơn hàng của tôi
    </a>
</div>

<div class="row g-4 align-items-start">
    <!-- CẬP NHẬT THÔNG TIN & ĐỊA CHỈ / SĐT -->
    <div class="col-lg-6">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Thông tin cá nhân</h2>

            <?php if (!empty($profileSuccess)): ?>
                <div class="alert alert-success border-coffee-soft small" role="alert">
                    <?= htmlspecialchars((string) $profileSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($profileError)): ?>
                <div class="alert alert-danger border-coffee-soft small" role="alert">
                    <?= htmlspecialchars((string) $profileError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/?c=account&a=updateProfile">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email</label>
                    <input
                        type="email"
                        class="form-control border-coffee-soft bg-light"
                        value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>"
                        disabled
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Họ và tên *</label>
                    <input
                        type="text"
                        name="ho_ten"
                        required
                        maxlength="100"
                        class="form-control border-coffee-soft"
                        value="<?= htmlspecialchars((string) ($user['ho_ten'] ?? '')) ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Số điện thoại</label>
                    <input
                        type="text"
                        name="so_dien_thoai"
                        maxlength="20"
                        class="form-control border-coffee-soft"
                        placeholder="Ví dụ: 0378095005"
                        value="<?= htmlspecialchars((string) ($user['so_dien_thoai'] ?? '')) ?>"
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Địa chỉ mặc định</label>
                    <textarea
                        name="dia_chi"
                        rows="2"
                        class="form-control border-coffee-soft"
                        placeholder="Số nhà, đường, phường/xã..."
                    ><?= htmlspecialchars((string) ($user['dia_chi'] ?? '')) ?></textarea>
                </div>

                <button type="submit" class="btn btn-moss rounded-pill px-4">
                    Lưu thông tin
                </button>
            </form>
        </div>
    </div>

    <!-- ĐỔI MẬT KHẨU -->
    <div class="col-lg-6">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Đổi mật khẩu</h2>

            <?php if (!empty($passwordSuccess)): ?>
                <div class="alert alert-success border-coffee-soft small" role="alert">
                    <?= htmlspecialchars((string) $passwordSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($passwordError)): ?>
                <div class="alert alert-danger border-coffee-soft small" role="alert">
                    <?= htmlspecialchars((string) $passwordError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/?c=account&a=changePassword">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Mật khẩu hiện tại *</label>
                    <input
                        type="password"
                        name="current_password"
                        required
                        class="form-control border-coffee-soft"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Mật khẩu mới *</label>
                    <input
                        type="password"
                        name="new_password"
                        required
                        minlength="6"
                        class="form-control border-coffee-soft"
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Nhập lại mật khẩu mới *</label>
                    <input
                        type="password"
                        name="confirm_password"
                        required
                        minlength="6"
                        class="form-control border-coffee-soft"
                    >
                </div>

                <button type="submit" class="btn btn-outline-dark rounded-pill px-4">
                    Cập nhật mật khẩu
                </button>
            </form>
        </div>
    </div>
</div>
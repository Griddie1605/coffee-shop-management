<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Thực đơn mẫu từ CSDL <code>coffee_shop</code></h1>
    <span class="badge text-bg-success">MVC + PDO OK</span>
</div>

<div class="row g-3">
    <?php foreach ($products as $item): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <span class="badge text-bg-secondary mb-2"><?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                    <h5 class="card-title"><?= htmlspecialchars($item['ten_san_pham']) ?></h5>
                    <p class="card-text text-muted small"><?= htmlspecialchars($item['mo_ta'] ?? '') ?></p>
                </div>
                <div class="card-footer bg-white fw-semibold">
                    <?= number_format((float) $item['gia_goc'], 0, ',', '.') ?>đ
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
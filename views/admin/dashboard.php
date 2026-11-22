<?php
$statusLabels = [
    'cho_xac_nhan' => ['label' => 'Chờ xác nhận', 'badge' => 'bg-warning text-dark'],
    'dang_pha_che' => ['label' => 'Đang pha chế', 'badge' => 'bg-info text-dark'],
    'dang_giao'    => ['label' => 'Đang giao',    'badge' => 'bg-primary'],
    'hoan_thanh'   => ['label' => 'Hoàn thành',   'badge' => 'bg-success'],
    'da_huy'       => ['label' => 'Đã hủy',       'badge' => 'bg-danger'],
];
?>



    
        <div class="mb-4">
            <h1 class="h2 mb-1">Admin Dashboard</h1>
            <p class="mb-0 text-coffee-muted">Tổng quan vận hành & thực đơn của 47th cafe</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card card-coffee p-3 h-100">
                    <div class="small text-coffee-muted">Tổng đơn hàng</div>
                    <div class="fs-3 fw-bold mt-1"><?= (int) ($orderStats['total_orders'] ?? 0) ?></div>
                    <div class="small text-coffee-muted mt-1">
                        Chờ xác nhận: <strong><?= (int) ($orderStats['pending_orders'] ?? 0) ?></strong>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card card-coffee p-3 h-100">
                    <div class="small text-coffee-muted">Đang pha chế</div>
                    <div class="fs-3 fw-bold mt-1"><?= (int) ($orderStats['brewing_orders'] ?? 0) ?></div>
                    <div class="small text-coffee-muted mt-1">Đơn cần xử lý tại quầy</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card card-coffee p-3 h-100">
                    <div class="small text-coffee-muted">Doanh thu hoàn tất</div>
                    <div class="fs-4 fw-bold price-moss mt-1">
                        <?= format_vnd((float) ($orderStats['completed_revenue'] ?? 0)) ?>
                    </div>
                    <div class="small text-coffee-muted mt-1">Đơn trạng thái Hoàn thành</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card card-coffee p-3 h-100">
                    <div class="small text-coffee-muted">Món đang mở bán</div>
                    <div class="fs-3 fw-bold mt-1">
                        <?= (int) ($catalogStats['active_products'] ?? 0) ?> / <?= (int) ($catalogStats['total_products'] ?? 0) ?>
                    </div>
                    <div class="small text-coffee-muted mt-1">
                        Danh mục mở bán: <strong><?= (int) ($catalogStats['active_categories'] ?? 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-coffee p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Đơn hàng gần đây</h2>
                <a href="<?= BASE_URL ?>/?c=admin&a=index" class="small fw-semibold text-dark">Xem tất cả</a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <p class="text-coffee-muted small mb-0">Chưa có đơn hàng nào.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead>
                            <tr class="text-coffee-muted">
                                <th>Mã đơn</th>
                                <th>Khách nhận</th>
                                <th>Thời gian</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ord): ?>
                                <?php
                                $st = (string) ($ord['trang_thai_don'] ?? 'cho_xac_nhan');
                                $meta = $statusLabels[$st] ?? ['label' => $st, 'badge' => 'bg-secondary'];
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) $ord['ma_don_hang']) ?></td>
                                    <td><?= htmlspecialchars((string) $ord['ten_nguoi_nhan']) ?></td>
                                    <td class="text-coffee-muted"><?= date('d/m/Y H:i', strtotime((string) $ord['ngay_tao'])) ?></td>
                                    <td class="text-end fw-semibold"><?= format_vnd((float) $ord['tong_thanh_toan']) ?></td>
                                    <td class="text-end">
                                        <span class="badge rounded-pill <?= $meta['badge'] ?>">
                                            <?= htmlspecialchars($meta['label']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    

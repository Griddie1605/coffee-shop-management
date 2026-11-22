<?php
$statusLabels = [
    'cho_xac_nhan' => ['Chờ xác nhận', 'bg-warning text-dark'],
    'dang_pha_che' => ['Đang pha chế', 'bg-info text-dark'],
    'dang_giao'    => ['Đang giao', 'bg-primary'],
    'hoan_thanh'   => ['Hoàn thành', 'bg-success'],
    'da_huy'       => ['Đã hủy', 'bg-danger'],
];
$completedRevenue = (float) ($orderStats['completed_revenue'] ?? 0);
$activeRevenue = (float) ($orderStats['active_revenue'] ?? 0);
$totalOrders = (int) ($orderStats['total_orders'] ?? 0);
$processingOrders = (int) ($orderStats['pending_orders'] ?? 0) + (int) ($orderStats['brewing_orders'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Thống kê doanh thu &amp; Báo cáo</h1>
        <p class="mb-0 text-coffee-muted">Theo dõi doanh thu, tình trạng đơn hàng và món bán chạy</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/?c=admin&a=dashboard&range=7d" class="btn btn-sm rounded-pill <?= ($range ?? '') === '7d' ? 'btn-dark' : 'btn-outline-dark' ?>">7 ngày</a>
        <a href="<?= BASE_URL ?>/?c=admin&a=dashboard&range=30d" class="btn btn-sm rounded-pill <?= ($range ?? '') === '30d' ? 'btn-dark' : 'btn-outline-dark' ?>">30 ngày</a>
        <a href="<?= BASE_URL ?>/?c=admin&a=dashboard" class="btn btn-sm rounded-pill <?= ($range ?? 'all') === 'all' ? 'btn-dark' : 'btn-outline-dark' ?>">Tất cả</a>
    </div>
</div>

<!-- BỘ LỌC NGÀY -->
<div class="card card-coffee p-3 mb-4">
    <form method="GET" action="<?= BASE_URL ?>/" class="row g-2 align-items-end">
        <input type="hidden" name="c" value="admin">
        <input type="hidden" name="a" value="dashboard">
        <div class="col-sm-4 col-md-3">
            <label class="form-label small fw-semibold mb-1">Từ ngày</label>
            <input type="date" name="from_date" class="form-control form-control-sm border-coffee-soft" value="<?= htmlspecialchars((string) ($fromDate ?? '')) ?>">
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label small fw-semibold mb-1">Đến ngày</label>
            <input type="date" name="to_date" class="form-control form-control-sm border-coffee-soft" value="<?= htmlspecialchars((string) ($toDate ?? '')) ?>">
        </div>
        <div class="col-sm-4 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-moss rounded-pill px-3">Lọc</button>
            <a href="<?= BASE_URL ?>/?c=admin&a=dashboard" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Đặt lại</a>
        </div>
        <div class="col-md-3 text-md-end small text-coffee-muted">
            Món đang bán: <strong><?= (int) ($catalogStats['active_products'] ?? 0) ?></strong>/<?= (int) ($catalogStats['total_products'] ?? 0) ?>
            · DM mở: <strong><?= (int) ($catalogStats['active_categories'] ?? 0) ?></strong>
        </div>
    </form>
</div>

<!-- 4 THẺ KPI -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Doanh thu hoàn thành</div>
            <div class="h4 fw-bold mt-1 mb-1"><?= format_vnd($completedRevenue) ?></div>
            <div class="small text-coffee-muted">Chưa hủy: <?= format_vnd($activeRevenue) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Tổng đơn hàng</div>
            <div class="h4 fw-bold mt-1 mb-1"><?= $totalOrders ?></div>
            <div class="small text-coffee-muted">Hoàn tất: <?= (int) ($orderStats['completed_orders'] ?? 0) ?> · Hủy: <?= (int) ($orderStats['cancelled_orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Đơn đang xử lý</div>
            <div class="h4 fw-bold mt-1 mb-1"><?= $processingOrders ?></div>
            <div class="small text-coffee-muted">Chờ xác nhận: <?= (int) ($orderStats['pending_orders'] ?? 0) ?> · Đang pha: <?= (int) ($orderStats['brewing_orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-coffee p-3 h-100">
            <div class="small text-coffee-muted">Giá trị đơn trung bình</div>
            <div class="h4 fw-bold mt-1 mb-1"><?= format_vnd((float) ($avgOrderValue ?? 0)) ?></div>
            <div class="small text-coffee-muted">Tính trên đơn không bị hủy</div>
        </div>
    </div>
</div>

<!-- DOANH THU THEO NGÀY & TOP MÓN BÁN CHẠY -->
<div class="row g-4 align-items-start mb-4">
    <div class="col-lg-6">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Doanh thu theo ngày</h2>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr class="text-coffee-muted">
                            <th>Ngày</th>
                            <th class="text-center">Số đơn</th>
                            <th class="text-end">Doanh thu (chưa hủy)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dailyRevenue)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-coffee-muted py-3">Chưa có dữ liệu đơn hàng.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dailyRevenue as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) $row['order_date']) ?></td>
                                    <td class="text-center"><?= (int) $row['orders_count'] ?></td>
                                    <td class="text-end fw-semibold"><?= format_vnd((float) $row['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-coffee p-4">
            <h2 class="h5 mb-3">Top sản phẩm bán chạy</h2>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr class="text-coffee-muted">
                            <th>Sản phẩm</th>
                            <th class="text-center">Đã bán</th>
                            <th class="text-end">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-coffee-muted py-3">Chưa có món nào được bán.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $idx => $tp): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border me-1"><?= $idx + 1 ?></span>
                                        <span class="fw-semibold"><?= htmlspecialchars((string) $tp['ten_san_pham']) ?></span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= (int) $tp['total_qty'] ?></td>
                                    <td class="text-end"><?= format_vnd((float) $tp['total_amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ĐƠN HÀNG GẦN ĐÂY -->
<div class="card card-coffee p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Đơn hàng gần đây</h2>
        <a href="<?= BASE_URL ?>/?c=admin&a=index" class="btn btn-sm btn-outline-dark rounded-pill px-3">Xem tất cả</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead>
                <tr class="text-coffee-muted">
                    <th>Mã đơn</th>
                    <th>Khách nhận</th>
                    <th>Thời gian</th>
                    <th class="text-end">Tổng tiền</th>
                    <th class="text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-coffee-muted py-3">Chưa có đơn hàng nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ord): ?>
                        <?php
                        $st = (string) ($ord['trang_thai_don'] ?? 'cho_xac_nhan');
                        [$stLabel, $stClass] = $statusLabels[$st] ?? [$st, 'bg-secondary'];
                        ?>
                        <tr>
                            <td class="fw-semibold">#<?= htmlspecialchars((string) $ord['ma_don_hang']) ?></td>
                            <td><?= htmlspecialchars((string) $ord['ten_nguoi_nhan']) ?></td>
                            <td class="text-coffee-muted"><?= htmlspecialchars((string) $ord['ngay_tao']) ?></td>
                            <td class="text-end fw-semibold"><?= format_vnd((float) $ord['tong_thanh_toan']) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $stClass ?>"><?= htmlspecialchars($stLabel) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
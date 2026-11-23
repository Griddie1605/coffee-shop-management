<?php
$statusLabels = [
    'cho_xac_nhan' => ['Chờ xác nhận', 'bg-warning text-dark'],
    'dang_pha_che' => ['Đang pha chế', 'bg-info text-dark'],
    'dang_giao'    => ['Đang giao', 'bg-primary'],
    'hoan_thanh'   => ['Hoàn thành', 'bg-success'],
    'da_huy'       => ['Đã hủy', 'bg-danger'],
];
?>

<div class="mb-4">
    <h1 class="h2 mb-1">Quản lý khách hàng</h1>
    <p class="mb-0 text-coffee-muted">Tra cứu thông tin khách hàng, chi tiêu tích lũy và lịch sử đơn hàng</p>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success border-coffee-soft" role="alert">
        <?= htmlspecialchars($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger border-coffee-soft" role="alert">
        <?= htmlspecialchars($flashError) ?>
    </div>
<?php endif; ?>

<div class="row g-4 align-items-start">
    <!-- DANH SÁCH KHÁCH HÀNG -->
    <div class="col-xl-7">
        <div class="card card-coffee p-4">
            <form method="GET" action="<?= BASE_URL ?>/" class="row g-2 mb-3">
                <input type="hidden" name="c" value="admin">
                <input type="hidden" name="a" value="customers">
                <div class="col-sm-8">
                    <input
                        type="text"
                        name="q"
                        class="form-control form-control-sm border-coffee-soft"
                        placeholder="Tìm theo tên, email hoặc số điện thoại..."
                        value="<?= htmlspecialchars($keyword) ?>"
                    >
                </div>
                <div class="col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-moss rounded-pill px-3 flex-grow-1">Tìm</button>
                    <?php if ($keyword !== ''): ?>
                        <a href="<?= BASE_URL ?>/?c=admin&a=customers" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Xóa</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0 small">
                    <thead>
                        <tr class="text-coffee-muted">
                            <th>Khách hàng</th>
                            <th>Liên hệ</th>
                            <th class="text-center">Đơn</th>
                            <th class="text-end">Chi tiêu</th>
                            <th class="text-center">Tài khoản</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-coffee-muted py-4">
                                    Không tìm thấy khách hàng nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $cust): ?>
                                <?php
                                $isActive = (int) ($cust['trang_thai_hoat_dong'] ?? 1) === 1;
                                $isSelected = !empty($selectedCustomer) && (int) $selectedCustomer['id'] === (int) $cust['id'];
                                $detailUrl = BASE_URL . '/?c=admin&a=customers&customer_id=' . (int) $cust['id']
                                    . ($keyword !== '' ? '&q=' . urlencode($keyword) : '');
                                ?>
                                <tr class="<?= $isSelected ? 'table-active' : '' ?>">
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars((string) $cust['ho_ten']) ?></div>
                                        <div class="text-coffee-muted">
                                            <?= htmlspecialchars((string) ($cust['dia_chi'] ?: 'Chưa cập nhật địa chỉ')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars((string) $cust['email']) ?></div>
                                        <div class="text-coffee-muted"><?= htmlspecialchars((string) ($cust['so_dien_thoai'] ?: '-')) ?></div>
                                    </td>
                                    <td class="text-center fw-semibold"><?= (int) $cust['total_orders'] ?></td>
                                    <td class="text-end fw-semibold"><?= format_vnd((float) $cust['total_spent']) ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $isActive ? 'Hoạt động' : 'Khóa' ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="<?= $detailUrl ?>" class="btn btn-sm btn-outline-dark rounded-pill px-2">
                                            Lịch sử
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=toggleCustomerStatus" class="d-inline">
                                            <input type="hidden" name="id" value="<?= (int) $cust['id'] ?>">
                                            <input type="hidden" name="q" value="<?= htmlspecialchars($keyword) ?>">
                                            <?php if (!empty($selectedCustomer)): ?>
                                                <input type="hidden" name="customer_id" value="<?= (int) $selectedCustomer['id'] ?>">
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                                                <?= $isActive ? 'Khóa' : 'Mở' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LỊCH SỬ MUA HÀNG CỦA KHÁCH ĐƯỢC CHỌN -->
    <div class="col-xl-5">
        <div class="card card-coffee p-4">
            <?php if (empty($selectedCustomer)): ?>
                <div class="text-center text-coffee-muted py-4">
                    Chọn <strong>Lịch sử</strong> ở một khách hàng bên trái để xem các đơn đã đặt.
                </div>
            <?php else: ?>
                <div class="border-bottom border-coffee-soft pb-3 mb-3">
                    <h2 class="h5 mb-1"><?= htmlspecialchars((string) $selectedCustomer['ho_ten']) ?></h2>
                    <div class="small text-coffee-muted">
                        <?= htmlspecialchars((string) $selectedCustomer['email']) ?>
                        <?php if (!empty($selectedCustomer['so_dien_thoai'])): ?>
                            · <?= htmlspecialchars((string) $selectedCustomer['so_dien_thoai']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($customerOrders)): ?>
                    <p class="text-coffee-muted small mb-0">Khách hàng này chưa có đơn hàng nào.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3" style="max-height: 520px; overflow-y: auto;">
                        <?php foreach ($customerOrders as $ord): ?>
                            <?php
                            $stKey = (string) ($ord['trang_thai_don'] ?? 'cho_xac_nhan');
                            [$stText, $stBadge] = $statusLabels[$stKey] ?? [$stKey, 'bg-secondary'];
                            $items = $orderItemsByOrderId[(int) $ord['id']] ?? [];
                            ?>
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold small">#<?= htmlspecialchars((string) $ord['ma_don_hang']) ?></span>
                                    <span class="badge rounded-pill <?= $stBadge ?>"><?= htmlspecialchars($stText) ?></span>
                                </div>
                                <div class="small text-coffee-muted mb-2">
                                    <?= htmlspecialchars((string) $ord['ngay_tao']) ?> ·
                                    <?= ($ord['hinh_thuc_nhan'] ?? '') === 'den_quan_lay' ? 'Đến quán lấy' : 'Giao tận nơi' ?>
                                </div>

                                <?php if (!empty($items)): ?>
                                    <ul class="list-unstyled small mb-2 ps-2 border-start border-2">
                                        <?php foreach ($items as $it): ?>
                                            <li>
                                                <?= htmlspecialchars((string) $it['ten_san_pham']) ?>
                                                <?= (int) $it['so_luong'] ?>
                                                — <span class="fw-semibold"><?= format_vnd((float) $it['thanh_tien']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center small pt-1 border-top">
                                    <span class="text-coffee-muted">Tổng thanh toán</span>
                                    <span class="fw-bold"><?= format_vnd((float) $ord['tong_thanh_toan']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
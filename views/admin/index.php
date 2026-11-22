<?php
$statusLabels = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'dang_pha_che' => 'Đang pha chế',
    'dang_giao'    => 'Đang giao',
    'hoan_thanh'   => 'Hoàn thành',
    'da_huy'       => 'Đã hủy',
];

$filters = [
    'all'          => 'Tất cả',
    'cho_xac_nhan' => 'Chờ xác nhận',
    'dang_pha_che' => 'Đang pha chế',
    'dang_giao'    => 'Đang giao',
    'hoan_thanh'   => 'Hoàn thành',
    'da_huy'       => 'Đã hủy',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Quản trị đơn hàng</h1>
        <p class="mb-0 text-coffee-muted">Cập nhật tiến trình xử lý đơn để đồng bộ trạng thái cho khách hàng</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($filters as $key => $label): ?>
            <a
                href="<?= BASE_URL ?>/?c=admin&a=index<?= $key !== 'all' ? '&status=' . urlencode($key) : '' ?>"
                class="btn btn-sm rounded-pill px-3 <?= ($statusFilter ?? 'all') === $key ? 'btn-moss' : 'btn-outline-dark' ?>"
            >
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
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

<?php if (empty($orders)): ?>
    <div class="card card-coffee p-5 text-center">
        <p class="text-coffee-muted mb-0">Không có đơn hàng nào trong bộ lọc hiện tại.</p>
    </div>
<?php else: ?>
    <div class="vstack gap-3">
        <?php foreach ($orders as $order): ?>
            <?php
            $oid = (int) $order['id'];
            $items = $itemsByOrder[$oid] ?? [];
            $curStatus = (string) ($order['trang_thai_don'] ?? 'cho_xac_nhan');
            ?>
            <div class="card card-coffee p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="fw-bold fs-5">
                            <?= htmlspecialchars((string) $order['ma_don_hang']) ?>
                            <span class="badge bg-secondary ms-2 small fw-normal">
                                <?= htmlspecialchars($statusLabels[$curStatus] ?? $curStatus) ?>
                            </span>
                        </div>
                        <div class="small text-coffee-muted">
                            <?= date('d/m/Y H:i', strtotime((string) $order['ngay_tao'])) ?>
                            · <?= htmlspecialchars((string) $order['ten_nguoi_nhan']) ?> (<?= htmlspecialchars((string) $order['sdt_nguoi_nhan']) ?>)
                            · <?= $order['hinh_thuc_nhan'] === 'den_quan_lay' ? 'Đến quán lấy' : 'Giao tận nơi' ?>
                            · <?= $order['phuong_thuc_thanh_toan'] === 'chuyen_khoan' ? 'Chuyển khoản' : 'Tiền mặt' ?>
                        </div>
                        <?php if ($order['hinh_thuc_nhan'] === 'giao_tan_noi' && !empty($order['dia_chi_giao'])): ?>
                            <div class="small text-coffee-muted">Địa chỉ: <?= htmlspecialchars((string) $order['dia_chi_giao']) ?></div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/?c=admin&a=updateStatus" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="order_id" value="<?= $oid ?>">
                        <input type="hidden" name="return_status" value="<?= htmlspecialchars((string) ($statusFilter ?? 'all')) ?>">
                        <select name="trang_thai_don" class="form-select form-select-sm border-coffee-soft" style="width: auto;">
                            <?php foreach ($statusLabels as $stKey => $stLabel): ?>
                                <option value="<?= $stKey ?>" <?= $curStatus === $stKey ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-moss rounded-pill px-3">Lưu</button>
                    </form>
                </div>

                <ul class="list-unstyled small mb-2">
                    <?php foreach ($items as $it): ?>
                        <li>
                            • <?= htmlspecialchars((string) $it['ten_san_pham']) ?>
                            <strong>x<?= (int) $it['so_luong'] ?></strong>
                            — <?= format_vnd((float) $it['thanh_tien']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($order['ghi_chu'])): ?>
                    <div class="small text-coffee-muted mb-2">Ghi chú: <?= htmlspecialchars((string) $order['ghi_chu']) ?></div>
                <?php endif; ?>

                <div class="text-end fw-bold pt-2 border-top border-coffee-soft">
                    Tổng thanh toán: <span class="price-moss"><?= format_vnd((float) $order['tong_thanh_toan']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
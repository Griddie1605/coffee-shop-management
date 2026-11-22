<?php
$statusMeta = [
    'cho_xac_nhan' => ['label' => 'Chờ xác nhận', 'badge' => 'bg-warning text-dark', 'step' => 1],
    'dang_pha_che' => ['label' => 'Đang pha chế', 'badge' => 'bg-info text-dark', 'step' => 2],
    'dang_giao'    => ['label' => 'Đang giao',    'badge' => 'bg-primary', 'step' => 3],
    'hoan_thanh'   => ['label' => 'Hoàn thành',   'badge' => 'bg-success', 'step' => 4],
    'da_huy'       => ['label' => 'Đã hủy',       'badge' => 'bg-danger', 'step' => 0],
];

$steps = [
    1 => 'Chờ xác nhận',
    2 => 'Đang pha chế',
    3 => 'Đang giao',
    4 => 'Hoàn thành',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <div>
        <h1 class="h2 mb-1">Đơn hàng của tôi</h1>
        <p class="mb-0 text-coffee-muted">Theo dõi trạng thái và lịch sử đặt món của bạn</p>
    </div>
    <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-moss rounded-pill px-4">
        Đặt thêm món
    </a>
</div>

<?php if (empty($orders)): ?>
    <div class="card card-coffee p-5 text-center">
        <p class="text-coffee-muted mb-3">Bạn chưa có đơn hàng nào được gắn với tài khoản này.</p>
        <div>
            <a href="<?= BASE_URL ?>/?c=home&a=menu" class="btn btn-moss rounded-pill px-4">
                Xem thực đơn ngay
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="vstack gap-4">
        <?php foreach ($orders as $order): ?>
            <?php
            $orderId = (int) $order['id'];
            $statusKey = (string) ($order['trang_thai_don'] ?? 'cho_xac_nhan');
            $meta = $statusMeta[$statusKey] ?? ['label' => $statusKey, 'badge' => 'bg-secondary', 'step' => 1];
            $currentStep = (int) $meta['step'];
            $orderItems = $itemsByOrder[$orderId] ?? [];
            ?>
            <div class="card card-coffee p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <div class="fw-bold fs-5">
                            Mã đơn: <?= htmlspecialchars((string) $order['ma_don_hang']) ?>
                        </div>
                        <div class="small text-coffee-muted">
                            Đặt lúc: <?= date('d/m/Y H:i', strtotime((string) $order['ngay_tao'])) ?>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-3 py-2 <?= $meta['badge'] ?>">
                        <?= htmlspecialchars($meta['label']) ?>
                    </span>
                </div>

                <?php if ($statusKey !== 'da_huy'): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($steps as $stepNum => $stepLabel): ?>
                            <?php $active = $stepNum <= $currentStep; ?>
                            <div class="col-6 col-md-3">
                                <div class="p-2 rounded text-center small border <?= $active ? 'bg-dark text-white border-dark fw-semibold' : 'text-coffee-muted border-coffee-soft' ?>">
                                    <?= $stepNum ?>. <?= htmlspecialchars($stepLabel) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="small text-coffee-muted">
                                <th>Món</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars((string) $item['ten_san_pham']) ?></td>
                                    <td class="small text-center"><?= (int) $item['so_luong'] ?></td>
                                    <td class="small text-end"><?= format_vnd((float) $item['don_gia']) ?></td>
                                    <td class="small text-end fw-semibold"><?= format_vnd((float) $item['thanh_tien']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 pt-2 border-top border-coffee-soft small">
                    <div class="col-md-7">
                        <div>
                            <strong>Người nhận:</strong>
                            <?= htmlspecialchars((string) $order['ten_nguoi_nhan']) ?> - <?= htmlspecialchars((string) $order['sdt_nguoi_nhan']) ?>
                        </div>
                        <div>
                            <strong>Hình thức:</strong>
                            <?= $order['hinh_thuc_nhan'] === 'den_quan_lay' ? 'Đến quán lấy' : 'Giao tận nơi' ?>
                            <?php if ($order['hinh_thuc_nhan'] === 'giao_tan_noi' && !empty($order['dia_chi_giao'])): ?>
                                (<?= htmlspecialchars((string) $order['dia_chi_giao']) ?>)
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong>Thanh toán:</strong>
                            <?= $order['phuong_thuc_thanh_toan'] === 'chuyen_khoan' ? 'Chuyển khoản' : 'Tiền mặt' ?>
                        </div>
                        <?php if (!empty($order['ghi_chu'])): ?>
                            <div>
                                <strong>Ghi chú:</strong> <?= htmlspecialchars((string) $order['ghi_chu']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-5 text-md-end">
                        <div>Tạm tính: <?= format_vnd((float) $order['tam_tinh']) ?></div>
                        <?php if ((float) $order['tien_giam'] > 0): ?>
                            <div class="text-success">Giảm giá: -<?= format_vnd((float) $order['tien_giam']) ?></div>
                        <?php endif; ?>
                        <div class="fs-6 fw-bold mt-1">
                            Tổng thanh toán: <span class="price-moss"><?= format_vnd((float) $order['tong_thanh_toan']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
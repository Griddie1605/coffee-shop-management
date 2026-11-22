<?php

/**
 * Lấy số liệu tổng hợp đơn hàng theo khoảng ngày tùy chọn.
 */
function getDashboardOrderStats(string $fromDate = '', string $toDate = ''): array
{
    $pdo = db_connect();
    $whereParts = [];
    $params = [];

    if ($fromDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) >= :from_date";
        $params[':from_date'] = $fromDate;
    }
    if ($toDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) <= :to_date";
        $params[':to_date'] = $toDate;
    }
    $whereSql = !empty($whereParts) ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

    $stmt = $pdo->prepare(
        "SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN dh.trang_thai_don = 'cho_xac_nhan' THEN 1 ELSE 0 END) AS pending_orders,
            SUM(CASE WHEN dh.trang_thai_don = 'dang_pha_che' THEN 1 ELSE 0 END) AS brewing_orders,
            SUM(CASE WHEN dh.trang_thai_don = 'hoan_thanh' THEN 1 ELSE 0 END) AS completed_orders,
            SUM(CASE WHEN dh.trang_thai_don = 'da_huy' THEN 1 ELSE 0 END) AS cancelled_orders,
            COALESCE(SUM(CASE WHEN dh.trang_thai_don = 'hoan_thanh' THEN dh.tong_thanh_toan ELSE 0 END), 0) AS completed_revenue,
            COALESCE(SUM(CASE WHEN dh.trang_thai_don != 'da_huy' THEN dh.tong_thanh_toan ELSE 0 END), 0) AS active_revenue,
            COUNT(CASE WHEN dh.trang_thai_don != 'da_huy' THEN 1 END) AS active_orders_count
         FROM don_hang dh
         {$whereSql}"
    );
    $stmt->execute($params);

    return $stmt->fetch() ?: [];
}

/**
 * Lấy số lượng danh mục và sản phẩm hiện hành trên hệ thống.
 */
function getDashboardCatalogStats(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT
            (SELECT COUNT(*) FROM danh_muc WHERE con_ban = 1) AS active_categories,
            (SELECT COUNT(*) FROM san_pham WHERE con_ban = 1) AS active_products,
            (SELECT COUNT(*) FROM san_pham) AS total_products"
    )->fetch() ?: [];
}

/**
 * Thống kê số đơn và doanh thu (loại trừ đơn hủy) nhóm theo từng ngày.
 */
function getDailyRevenueReport(string $fromDate = '', string $toDate = '', int $limit = 15): array
{
    $pdo = db_connect();
    $whereParts = [];
    $params = [];

    if ($fromDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) >= :from_date";
        $params[':from_date'] = $fromDate;
    }
    if ($toDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) <= :to_date";
        $params[':to_date'] = $toDate;
    }
    $whereSql = !empty($whereParts) ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

    $stmt = $pdo->prepare(
        "SELECT
            DATE(dh.ngay_tao) AS order_date,
            COUNT(*) AS orders_count,
            COALESCE(SUM(CASE WHEN dh.trang_thai_don != 'da_huy' THEN dh.tong_thanh_toan ELSE 0 END), 0) AS revenue
         FROM don_hang dh
         {$whereSql}
         GROUP BY DATE(dh.ngay_tao)
         ORDER BY order_date DESC
         LIMIT :limit"
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lấy top sản phẩm bán chạy theo số lượng (không tính đơn đã hủy).
 */
function getTopSellingProducts(string $fromDate = '', string $toDate = '', int $limit = 8): array
{
    $pdo = db_connect();
    $whereParts = ["dh.trang_thai_don != 'da_huy'"];
    $params = [];

    if ($fromDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) >= :from_date";
        $params[':from_date'] = $fromDate;
    }
    if ($toDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) <= :to_date";
        $params[':to_date'] = $toDate;
    }
    $whereSql = ' WHERE ' . implode(' AND ', $whereParts);

    $stmt = $pdo->prepare(
        "SELECT
            ct.ten_san_pham,
            COALESCE(SUM(ct.so_luong), 0) AS total_qty,
            COALESCE(SUM(ct.thanh_tien), 0) AS total_amount
         FROM chi_tiet_don_hang ct
         INNER JOIN don_hang dh ON dh.id = ct.don_hang_id
         {$whereSql}
         GROUP BY ct.san_pham_id, ct.ten_san_pham
         ORDER BY total_qty DESC, total_amount DESC
         LIMIT :limit"
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Lấy danh sách đơn hàng gần đây phục vụ bảng tổng quan trên Dashboard.
 */
function getRecentDashboardOrders(string $fromDate = '', string $toDate = '', int $limit = 6): array
{
    $pdo = db_connect();
    $whereParts = [];
    $params = [];

    if ($fromDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) >= :from_date";
        $params[':from_date'] = $fromDate;
    }
    if ($toDate !== '') {
        $whereParts[] = "DATE(dh.ngay_tao) <= :to_date";
        $params[':to_date'] = $toDate;
    }
    $whereSql = !empty($whereParts) ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

    $stmt = $pdo->prepare(
        "SELECT dh.id, dh.ma_don_hang, dh.ten_nguoi_nhan, dh.tong_thanh_toan, dh.trang_thai_don, dh.ngay_tao
         FROM don_hang dh
         {$whereSql}
         ORDER BY dh.ngay_tao DESC, dh.id DESC
         LIMIT :limit"
    );
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
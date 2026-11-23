<?php

require_once __DIR__ . '/../../models/ReportModel.php';

/**
 * Hiển thị báo cáo doanh thu, tình trạng đơn, top món bán chạy và đơn gần đây.
 */
function adminDashboard(): void
{
    adminRequireStaff();
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    $range = (string) ($_GET['range'] ?? 'all');
    $fromDate = trim((string) ($_GET['from_date'] ?? ''));
    $toDate = trim((string) ($_GET['to_date'] ?? ''));

    $validDate = static fn(string $d): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
    $today = date('Y-m-d');

    if ($range === '7d') {
        $fromDate = date('Y-m-d', strtotime($today . ' -6 days'));
        $toDate = $today;
    } elseif ($range === '30d') {
        $fromDate = date('Y-m-d', strtotime($today . ' -29 days'));
        $toDate = $today;
    } else {
        $fromDate = $validDate($fromDate) ? $fromDate : '';
        $toDate = $validDate($toDate) ? $toDate : '';
        $range = ($fromDate !== '' || $toDate !== '') ? 'custom' : 'all';
    }

    $orderStats = getDashboardOrderStats($fromDate, $toDate);
    $catalogStats = getDashboardCatalogStats();

    $activeOrdersCount = (int) ($orderStats['active_orders_count'] ?? 0);
    $activeRevenue = (float) ($orderStats['active_revenue'] ?? 0);
    $avgOrderValue = $activeOrdersCount > 0 ? ($activeRevenue / $activeOrdersCount) : 0.0;

    render('admin/dashboard', [
        'pageTitle' => 'Thống kê & Báo cáo - CF Song Thư',
        'orderStats' => $orderStats,
        'catalogStats' => $catalogStats,
        'avgOrderValue' => $avgOrderValue,
        'dailyRevenue' => getDailyRevenueReport($fromDate, $toDate, 15),
        'topProducts' => getTopSellingProducts($fromDate, $toDate, 8),
        'recentOrders' => getRecentDashboardOrders($fromDate, $toDate, 6),
        'range' => $range,
        'fromDate' => $fromDate,
        'toDate' => $toDate,
    ]);
}
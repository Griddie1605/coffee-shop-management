<?php
$curA = $_GET['a'] ?? 'dashboard';
$navItems = [
    'dashboard' => ['label' => 'Tổng quan',              'href' => BASE_URL . '/?c=admin&a=dashboard'],
    'index'     => ['label' => 'Quản trị đơn hàng',      'href' => BASE_URL . '/?c=admin&a=index'],
    'catalog'   => ['label' => 'Quản lý món & danh mục', 'href' => BASE_URL . '/?c=admin&a=catalog'],
];
?>

<aside class="admin-sidebar border-end">
    <div class="admin-sidebar-inner py-4 px-3">
        <div class="small text-uppercase fw-bold text-coffee-muted px-3 mb-2">
            Quản trị quán
        </div>
        <nav class="nav flex-column gap-1">
            <?php foreach ($navItems as $key => $item): ?>
                <?php $isActive = ($curA === $key); ?>
                <a
                    href="<?= $item['href'] ?>"
                    class="nav-link px-3 py-2 rounded small <?= $isActive ? 'bg-dark text-white fw-semibold' : 'text-dark' ?>"
                >
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
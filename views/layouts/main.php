<?php
$currentC = $_GET['c'] ?? 'home';
$currentA = $_GET['a'] ?? 'index';
$isHomePage = ($currentC === 'home' && $currentA === 'index');
$cartCount = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? '47th cafe - Trang chủ') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-cafe-top sticky-top py-3">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand font-serif-brand fs-2 fw-semibold lh-1" href="<?= BASE_URL ?>/">
                Song Thư
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Mở thanh điều hướng">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3 my-2 my-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $isHomePage ? 'active' : '' ?>" href="<?= BASE_URL ?>/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/#eat-section">This Is Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentC === 'home' && $currentA === 'menu') ? 'active' : '' ?>" href="<?= BASE_URL ?>/?c=home&a=menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/#contact-section">Contact Us</a>
                    </li>
                    <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-inline-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="login-dot"></span>
                            <span><?= htmlspecialchars((string) ($_SESSION['user']['ho_ten'] ?? 'Tài khoản')) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-coffee-soft">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/?c=order&a=index">Đơn hàng của tôi</a>
                            </li>
                            <?php if (in_array((string) ($_SESSION['user']['vai_tro'] ?? ''), ['quan_tri', 'nhan_vien'], true)): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/?c=admin&a=dashboard">Admin Dashboard</a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/?c=auth&a=logout">Đăng xuất</a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link d-inline-flex align-items-center gap-1" href="<?= BASE_URL ?>/?c=auth&a=login">
                            <span class="login-dot"></span>
                            <span>Log In</span>
                        </a>
                    </li>
                <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?c=cart&a=index">Cart (<?= $cartCount ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($isHomePage): ?>
        <main>
            <?= $content ?>
        </main>
    <?php elseif ($currentC === 'admin'): ?>
        <main class="admin-shell d-flex flex-column flex-lg-row">
            <?php require __DIR__ . '/../admin/_sidebar.php'; ?>
            <div class="admin-content flex-grow-1 p-4 p-lg-5">
                <?= $content ?>
            </div>
        </main>
    <?php else: ?>
        <main class="container py-4 pb-5">
            <?= $content ?>
        </main>
    <?php endif; ?>

    <footer id="contact-section" class="footer-moss-wrap">
    <div class="footer-moss-main">
        <div class="container-fluid px-lg-5">
            <div class="row gy-4 align-items-start justify-content-between">
                <div class="col-12 col-lg-5">
                    <h3 class="footer-heading">Newsletter</h3>
                    <p class="footer-subtext">Stay up to date with all the latest from 47th Cafe</p>
                    <form method="POST" action="#" onsubmit="return false;" class="footer-newsletter-form">
                        <label for="newsletterEmail" class="form-label footer-email-label">Email *</label>
                        <input type="email" id="newsletterEmail" class="form-control footer-input">

                        <div class="footer-agree-row">
                            <div class="form-check footer-check-wrap">
                                <input class="form-check-input footer-checkbox" type="checkbox" id="newsletterAgree">
                                <label class="form-check-label footer-agree-text" for="newsletterAgree">
                                    Yes, subscribe me to your newsletter. *
                                </label>
                            </div>
                            <button type="submit" class="btn btn-footer-outline">Join</button>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-lg-3">
                    <h3 class="footer-heading">Talk to Us</h3>
                    <p class="footer-subtext footer-contact-group">
                        123-456-7890<br>
                        info@mysite.com
                    </p>
                    <p class="footer-subtext mb-0">
                        500 Terry Francine St.<br>
                        San Francisco, CA 94158
                    </p>
                </div>

                <div class="col-12 col-lg-4 text-lg-end">
                    <ul class="list-unstyled footer-social-list mb-0">
                        <li><a href="#contact-section">Instagram</a></li>
                        <li><a href="#contact-section">Facebook</a></li>
                        <li><a href="#contact-section">Twitter</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-copyright-bar py-2 px-3 text-center small">
        &copy; <?= date('Y') ?> by 47th cafe. Coffee Shop Management System.
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
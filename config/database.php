<?php

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'coffee_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// BASE_URL được suy ra từ thư mục hiện hành để route hoạt động khi project chạy trong XAMPP subfolder.
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(dirname($scriptName), '/');
define('BASE_URL', $basePath === '' ? '' : $basePath);
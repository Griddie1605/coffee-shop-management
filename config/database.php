<?php

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'coffee_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Tu dong lay thu muc goc cua du an (vd: /DoAn_coffee tren Mac hoac /coffee-shop-management tren Windows)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(dirname($scriptName), '/');
define('BASE_URL', $basePath === '' ? '' : $basePath);
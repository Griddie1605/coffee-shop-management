<?php

declare(strict_types=1);

// Hiển thị lỗi qua log thay vì rò rỉ thông tin hệ thống ra trình duyệt.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/view.php';

$controller = $_GET['c'] ?? 'home';
$action = $_GET['a'] ?? 'index';

$controllerSlug = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($controller));
$actionSlug = preg_replace('/[^a-zA-Z0-9_]/', '', $action);

if ($controllerSlug === '') {
    $controllerSlug = 'home';
}
if ($actionSlug === '') {
    $actionSlug = 'index';
}

$controllerName = ucfirst($controllerSlug) . 'Controller';
$controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo '404 - Không tìm thấy controller: ' . htmlspecialchars($controllerName);
    exit;
}

require_once $controllerFile;

$handlerFunc = $controllerSlug . ucfirst($actionSlug);
if (!function_exists($handlerFunc)) {
    http_response_code(404);
    echo '404 - Không tìm thấy action: ' . htmlspecialchars($handlerFunc);
    exit;
}

$handlerFunc();
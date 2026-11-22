<?php

/**
 * Định dạng số tiền theo đơn vị tiền Việt Nam.
 */
function format_vnd(float $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

/**
 * Escape dữ liệu trước khi đưa ra HTML; dùng cho mọi chuỗi lấy từ người dùng hoặc CSDL.
 */
function html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Render view trong layout. Dữ liệu được truyền vào chỉ phục vụ lần render hiện tại.
 */
function render(string $viewName, array $data = [], string $layout = 'layouts/main'): void
{
    extract($data, EXTR_SKIP);

    $viewFile = __DIR__ . '/../views/' . ltrim($viewName, '/') . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(404);
        echo 'Không tìm thấy view: ' . htmlspecialchars($viewName);
        return;
    }

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    $layoutFile = __DIR__ . '/../views/' . ltrim($layout, '/') . '.php';
    if ($layout !== '' && file_exists($layoutFile)) {
        require $layoutFile;
    } else {
        echo $content;
    }
}
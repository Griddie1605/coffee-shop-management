<?php

function format_vnd(float $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

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
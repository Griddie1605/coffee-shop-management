<?php

abstract class Controller
{
    protected function view(string $viewPath, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../views/' . ltrim($viewPath, '/') . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            echo "Không tìm thấy view: " . htmlspecialchars($viewPath);
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

    protected function redirect(string $path): void
    {
        $target = str_starts_with($path, 'http') ? $path : BASE_URL . '/' . ltrim($path, '/');
        header('Location: ' . $target);
        exit;
    }
}
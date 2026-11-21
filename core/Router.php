<?php

class Router
{
    public function dispatch(): void
    {
        $controllerParam = $_GET['c'] ?? 'home';
        $actionParam = $_GET['a'] ?? 'index';

        $controllerSlug = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($controllerParam));
        $actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionParam);

        if ($controllerSlug === '') {
            $controllerSlug = 'home';
        }
        if ($actionName === '') {
            $actionName = 'index';
        }

        $controllerClass = ucfirst($controllerSlug) . 'Controller';
        $controllerFile = __DIR__ . '/../controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "404 - Không tìm thấy Controller: " . htmlspecialchars($controllerClass);
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Lỗi: Class " . htmlspecialchars($controllerClass) . " không tồn tại.";
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionName)) {
            http_response_code(404);
            echo "404 - Không tìm thấy hành động: " . htmlspecialchars($actionName);
            return;
        }

        $controller->$actionName();
    }
}
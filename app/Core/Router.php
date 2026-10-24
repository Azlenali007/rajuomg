<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight Core PHP Router with CSRF check and error dispatch
 */
class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, callable|array $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(string $method, string $uri): void
    {
        $parsedUrl = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($parsedUrl, '/') ?: '/';

        $lookupMethod = ($method === 'HEAD') ? 'GET' : $method;
        $handler = self::$routes[$lookupMethod][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/resources/views/errors/404.php';
            exit;
        }

        // Automatic CSRF check for state-changing web POST requests (exclude webhooks and installer)
        if ($method === 'POST' && !str_starts_with($path, '/api/webhook') && !str_starts_with($path, '/install')) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!CSRF::validate($token)) {
                http_response_code(419);
                if (str_starts_with($path, '/api/')) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch or expired']);
                } else {
                    echo "419 Page Expired. Please refresh and try again.";
                }
                exit;
            }
        }

        if (is_array($handler)) {
            [$controllerClass, $action] = $handler;
            $controller = new $controllerClass();
            $controller->$action();
        } else {
            call_user_func($handler);
        }
    }
}

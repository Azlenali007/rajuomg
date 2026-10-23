<?php
declare(strict_types=1);

/**
 * OTP Doctor - Public Front Controller
 */

// Error Handling Configuration (Fail-safe, no secrets exposed)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Security Response Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Check if application is installed; if not, redirect to Web Installer
$lockFile = dirname(__DIR__) . '/storage/installed.lock';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!file_exists($lockFile) && !str_starts_with($requestUri, '/install')) {
    header('Location: /install/index.php');
    exit;
}

// Autoloader for application namespaces
spl_autoload_register(function (string $class) {
    $prefixApp = 'App\\';
    $baseDirApp = dirname(__DIR__) . '/app/';

    if (str_starts_with($class, $prefixApp)) {
        $relativeClass = substr($class, strlen($prefixApp));
        $file = $baseDirApp . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    $prefixMod = 'App\\Modules\\';
    $baseDirMod = dirname(__DIR__) . '/modules/';
    if (str_starts_with($class, $prefixMod)) {
        $relativeClass = substr($class, strlen($prefixMod));
        $file = $baseDirMod . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load Configuration & Helpers
\App\Core\Session::start();

// Load Routes
require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';
require_once dirname(__DIR__) . '/routes/admin.php';

// Dispatch Request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

\App\Core\Router::dispatch($method, $uri);

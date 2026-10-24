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

$rootDir = dirname(__DIR__);
$lockFile = $rootDir . '/storage/installed.lock';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// 1. Direct handling of installer requests
if ($requestUri === '/install' || $requestUri === '/install/' || str_starts_with($requestUri, '/install/')) {
    require_once __DIR__ . '/install/index.php';
    exit;
}

// 2. Check if application is installed; if not, redirect to Web Installer with fallback UI
if (!file_exists($lockFile)) {
    if (!headers_sent()) {
        header('Location: /install/index.php');
    }
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0;url=/install/index.php">
    <title>Redirecting to Installer — OTP Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6 text-center font-sans">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-purple-100">
        <div class="w-16 h-16 bg-purple-100 text-purple-700 rounded-2xl flex items-center justify-center mx-auto mb-4 font-extrabold text-2xl">OD</div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Setup Required</h1>
        <p class="text-slate-600 mb-6 text-sm leading-relaxed">OTP Doctor is not configured yet. Complete the web installation wizard to set up your database, initial settings, and Super Admin account.</p>
        <a href="/install/index.php" class="inline-flex items-center justify-center w-full px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow-md transition-all">Launch Installer &rarr;</a>
    </div>
</body>
</html>';
    exit;
}

// 3. Autoloader for application namespaces
spl_autoload_register(function (string $class) use ($rootDir) {
    $prefixApp = 'App\\';
    $baseDirApp = $rootDir . '/app/';

    if (str_starts_with($class, $prefixApp)) {
        $relativeClass = substr($class, strlen($prefixApp));
        $file = $baseDirApp . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    $prefixMod = 'App\\Modules\\';
    $baseDirMod = $rootDir . '/modules/';
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
require_once $rootDir . '/routes/web.php';
require_once $rootDir . '/routes/api.php';
require_once $rootDir . '/routes/admin.php';

// Dispatch Request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    \App\Core\Router::dispatch($method, $uri);
} catch (\Throwable $e) {
    error_log("Unhandled Application Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    $errorView = $rootDir . '/resources/views/errors/500.php';
    if (file_exists($errorView)) {
        require $errorView;
    } else {
        echo '<!DOCTYPE html><html><head><title>500 Internal Error</title></head><body style="font-family:sans-serif;padding:40px;text-align:center;"><h2>500 Server Error</h2><p>An unexpected error occurred. Please try again later.</p></body></html>';
    }
    exit;
}

<?php
declare(strict_types=1);

/**
 * OTP Doctor - Built-in CLI Server Router
 * Directs traffic on port 3000 to public entry points and serves static assets reliably.
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// 1. Direct handling for web installer: /install, /install/, /install/index.php
if ($parsedPath === '/install' || $parsedPath === '/install/' || $parsedPath === '/install/index.php') {
    $_SERVER['SCRIPT_NAME'] = '/install/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/install/index.php';
    require __DIR__ . '/public/install/index.php';
    return true;
}

// 2. Serve static assets directly from public/ directory
$publicFilePath = __DIR__ . '/public' . $parsedPath;
if ($parsedPath !== '/' && is_file($publicFilePath)) {
    $extension = strtolower(pathinfo($publicFilePath, PATHINFO_EXTENSION));
    
    // If someone requests public/index.php or any other php file directly, execute it
    if ($extension === 'php') {
        $_SERVER['SCRIPT_NAME'] = $parsedPath;
        $_SERVER['SCRIPT_FILENAME'] = $publicFilePath;
        require $publicFilePath;
        return true;
    }

    $mimeTypes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'txt'   => 'text/plain; charset=UTF-8',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }
    header('Content-Length: ' . (string)filesize($publicFilePath));
    header('Cache-Control: public, max-age=86400');
    readfile($publicFilePath);
    return true;
}

// 3. Fallback to public/index.php front controller
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
require __DIR__ . '/public/index.php';
return true;

<?php
declare(strict_types=1);

/**
 * OTP Doctor - Direct Login Entry Point
 */
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/login.php') {
    $_SERVER['REQUEST_URI'] = '/login';
}

require_once __DIR__ . '/index.php';

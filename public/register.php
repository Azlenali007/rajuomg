<?php
declare(strict_types=1);

/**
 * OTP Doctor - Direct Register Entry Point
 */
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/register.php') {
    $_SERVER['REQUEST_URI'] = '/register';
}

require_once __DIR__ . '/index.php';

<?php
declare(strict_types=1);

/**
 * OTP Platform - Application Configuration
 */

return [
    'name'        => getenv('APP_NAME') ?: 'OTP Doctor Verification',
    'env'         => getenv('APP_ENV') ?: 'production',
    'debug'       => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
    'url'         => getenv('APP_URL') ?: 'https://example.com',
    'timezone'    => 'UTC',
    'currency'    => 'USD',
    'currency_sym'=> '$',
    
    // Order configuration
    'order_timeout_minutes' => 15, // Auto-cancel if no OTP after 15 mins
    'poll_interval_seconds' => 3,  // Recommended frontend polling interval
    
    // Security & Session
    'session_lifetime' => 86400, // 24 hours
    'password_algo'    => PASSWORD_BCRYPT,
    'password_options' => ['cost' => 12],
    
    // Storage
    'log_path'    => dirname(__DIR__) . '/storage/logs/app.log',
    'upload_path' => dirname(__DIR__) . '/storage/uploads',
];

<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\OrderController;
use App\Controllers\WalletController;

// Authentication routes
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'processLogin']);
Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'processRegister']);
Router::get('/logout', [AuthController::class, 'logout']);

// User Application routes
Router::get('/', [OrderController::class, 'showServices']);
Router::get('/dashboard', [OrderController::class, 'showServices']);
Router::get('/services', [OrderController::class, 'showServices']);
Router::get('/orders', [OrderController::class, 'listOrders']);
Router::get('/order', [OrderController::class, 'showOrder']);
Router::get('/wallet', [WalletController::class, 'showWallet']);
Router::post('/wallet/manual', [WalletController::class, 'submitManual']);

// Web-Based Installation Wizard route
Router::get('/install', function() {
    require_once dirname(__DIR__) . '/public/install/index.php';
    exit;
});


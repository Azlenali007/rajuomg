<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\OrderController;
use App\Controllers\WalletController;

// Authentication routes
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'processLogin']);
Router::get('/login.php', [AuthController::class, 'showLogin']);
Router::post('/login.php', [AuthController::class, 'processLogin']);

Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'processRegister']);
Router::get('/register.php', [AuthController::class, 'showRegister']);
Router::post('/register.php', [AuthController::class, 'processRegister']);

Router::get('/logout', [AuthController::class, 'logout']);
Router::get('/logout.php', [AuthController::class, 'logout']);

// User Application & Landing Page routes
Router::get('/', [OrderController::class, 'showServices']);
Router::get('/dashboard', [OrderController::class, 'showServices']);
Router::get('/dashboard.php', [OrderController::class, 'showServices']);
Router::get('/services', [OrderController::class, 'showServices']);
Router::get('/services.php', [OrderController::class, 'showServices']);
Router::get('/orders', [OrderController::class, 'listOrders']);
Router::get('/orders.php', [OrderController::class, 'listOrders']);
Router::get('/order', [OrderController::class, 'showOrder']);
Router::get('/order.php', [OrderController::class, 'showOrder']);
Router::get('/wallet', [WalletController::class, 'showWallet']);
Router::get('/wallet.php', [WalletController::class, 'showWallet']);
Router::post('/wallet/manual', [WalletController::class, 'submitManual']);

// Web-Based Installation Wizard routes (handles GET, POST, trailing slash, and index.php)
$installHandler = function() {
    require_once dirname(__DIR__) . '/public/install/index.php';
    exit;
};

Router::get('/install', $installHandler);
Router::post('/install', $installHandler);
Router::get('/install/', $installHandler);
Router::post('/install/', $installHandler);
Router::get('/install/index.php', $installHandler);
Router::post('/install/index.php', $installHandler);

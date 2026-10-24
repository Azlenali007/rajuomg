<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\AdminController;

Router::get('/admin', [AdminController::class, 'dashboard']);
Router::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Router::get('/admin/dashboard.php', [AdminController::class, 'dashboard']);
Router::get('/admin/users', [AdminController::class, 'users']);
Router::get('/admin/users.php', [AdminController::class, 'users']);
Router::post('/admin/users/toggle', [AdminController::class, 'toggleUserStatus']);
Router::post('/admin/users/adjust-wallet', [AdminController::class, 'adjustWallet']);

Router::get('/admin/providers', [AdminController::class, 'providers']);
Router::get('/admin/providers.php', [AdminController::class, 'providers']);
Router::post('/admin/providers/test', [AdminController::class, 'testProvider']);
Router::post('/admin/providers/sync', [AdminController::class, 'syncNow']);

Router::get('/admin/services', [AdminController::class, 'services']);
Router::get('/admin/services.php', [AdminController::class, 'services']);
Router::post('/admin/services/price', [AdminController::class, 'updateServicePrice']);

Router::get('/admin/orders', [AdminController::class, 'orders']);
Router::get('/admin/orders.php', [AdminController::class, 'orders']);

Router::get('/admin/payments', [AdminController::class, 'payments']);
Router::get('/admin/payments.php', [AdminController::class, 'payments']);
Router::post('/admin/payments/review', [AdminController::class, 'reviewManualPayment']);

<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\OrderController;
use App\Controllers\WalletController;

// AJAX & REST Endpoints
Router::post('/api/order/create', [OrderController::class, 'placeOrder']);
Router::get('/api/order/status', [OrderController::class, 'pollStatus']);
Router::post('/api/order/cancel', [OrderController::class, 'cancel']);

// Payment Endpoints
Router::post('/api/payment/razorpay/init', [WalletController::class, 'initRazorpay']);
Router::post('/api/payment/razorpay/verify', [WalletController::class, 'verifyRazorpay']);

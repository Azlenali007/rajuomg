<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Services\OrderService;
use App\Services\WalletService;
use PDO;

class OrderController
{
    protected PDO $pdo;
    protected OrderService $orderService;
    protected WalletService $walletService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->orderService = new OrderService($this->pdo);
        $this->walletService = new WalletService($this->pdo);
    }

    /**
     * Display live services catalog for ordering (public landing page & dashboard)
     */
    public function showServices(): void
    {
        $userId = Auth::id();
        $wallet = $userId ? $this->walletService->getWallet($userId) : null;

        $services = $this->pdo->query("
            SELECT s.*, 
                   COUNT(ps.id) as provider_count,
                   MIN(ps.provider_cost) as min_cost,
                   MAX(ps.stock_status) as stock_state
            FROM services s
            LEFT JOIN provider_services ps ON s.id = ps.service_id
            WHERE s.is_active = 1
            GROUP BY s.id
            ORDER BY s.service_name ASC
        ")->fetchAll();

        $countries = $this->pdo->query("SELECT * FROM countries WHERE is_active = 1 ORDER BY country_name ASC")->fetchAll();

        View::render('user/services', [
            'services' => $services,
            'countries' => $countries,
            'wallet' => $wallet
        ], 'user');
    }

    /**
     * Show single order status page with live polling
     */
    public function showOrder(): void
    {
        Auth::requireLogin();
        $orderId = (int)($_GET['id'] ?? 0);
        $userId = (int)Auth::id();

        $stmt = $this->pdo->prepare("
            SELECT o.*, s.service_name, c.country_name, c.dial_code
            FROM orders o
            JOIN services s ON o.service_id = s.id
            JOIN countries c ON o.country_id = c.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            header('Location: /orders');
            exit;
        }

        View::render('user/order', [
            'order' => $order
        ], 'user');
    }

    /**
     * AJAX endpoint to place order
     */
    public function placeOrder(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = (int)Auth::id();
        $serviceId = (int)($_POST['service_id'] ?? 0);
        $countryId = !empty($_POST['country_id']) ? (int)$_POST['country_id'] : null;

        $result = $this->orderService->createOrder($userId, $serviceId, $countryId);
        echo json_encode($result);
        exit;
    }

    /**
     * AJAX endpoint to poll live OTP status
     */
    public function pollStatus(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = (int)Auth::id();
        $orderId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

        $result = $this->orderService->checkStatus($orderId, $userId);
        echo json_encode($result);
        exit;
    }

    /**
     * AJAX endpoint to cancel order
     */
    public function cancel(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = (int)Auth::id();
        $orderId = (int)($_POST['order_id'] ?? 0);

        $result = $this->orderService->cancelOrder($orderId, $userId, 'Cancelled by user request');
        echo json_encode($result);
        exit;
    }

    /**
     * Order history listing
     */
    public function listOrders(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();

        $stmt = $this->pdo->prepare("
            SELECT o.*, s.service_name, c.country_name 
            FROM orders o
            JOIN services s ON o.service_id = s.id
            JOIN countries c ON o.country_id = c.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();

        View::render('user/orders', ['orders' => $orders], 'user');
    }
}

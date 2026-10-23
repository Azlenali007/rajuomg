<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Modules\Payments\ManualQrService;
use App\Modules\Providers\OtpDoctorProvider;
use App\Services\OrderService;
use App\Services\SyncService;
use App\Services\WalletService;
use PDO;

class AdminController
{
    protected PDO $pdo;
    protected WalletService $walletService;
    protected SyncService $syncService;
    protected OrderService $orderService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->walletService = new WalletService($this->pdo);
        $this->syncService = new SyncService($this->pdo);
        $this->orderService = new OrderService($this->pdo);
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard(): void
    {
        Auth::requireSuperAdmin();

        $stats = [
            'total_users' => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
            'total_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'active_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ACTIVE'")->fetchColumn(),
            'received_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('RECEIVED', 'COMPLETED')")->fetchColumn(),
            'cancelled_orders' => (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'CANCELLED'")->fetchColumn(),
            'total_revenue' => (float)$this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE type = 'credit' AND status = 'completed'")->fetchColumn(),
            'estimated_profit' => (float)$this->pdo->query("SELECT COALESCE(SUM(price - provider_cost), 0) FROM orders WHERE status IN ('RECEIVED', 'COMPLETED')")->fetchColumn(),
            'pending_manual_payments' => (int)$this->pdo->query("SELECT COUNT(*) FROM manual_payment_requests WHERE status = 'PENDING'")->fetchColumn()
        ];

        // Fetch recent orders
        $recentOrders = $this->pdo->query("
            SELECT o.*, u.name as user_name, u.email as user_email, s.service_name 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN services s ON o.service_id = s.id
            ORDER BY o.created_at DESC 
            LIMIT 10
        ")->fetchAll();

        // Fetch primary provider status
        $provider = $this->pdo->query("SELECT * FROM providers LIMIT 1")->fetch();

        View::render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'provider' => $provider
        ], 'admin');
    }

    /**
     * User Management
     */
    public function users(): void
    {
        Auth::requireSuperAdmin();

        $search = trim((string)($_GET['q'] ?? ''));
        $query = "
            SELECT u.*, w.balance, COUNT(o.id) as order_count 
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            LEFT JOIN orders o ON u.id = o.user_id
            WHERE u.role = 'user'
        ";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT 100";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        View::render('admin/users', ['users' => $users, 'search' => $search], 'admin');
    }

    /**
     * Toggle User Suspension
     */
    public function toggleUserStatus(): void
    {
        Auth::requireSuperAdmin();
        $userId = (int)($_POST['user_id'] ?? 0);
        $adminId = (int)Auth::id();

        $stmt = $this->pdo->prepare("SELECT status FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
            $this->pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $userId]);

            // Audit
            $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'TOGGLE_USER_STATUS', 'users', ?, ?, NOW())
            ")->execute([$adminId, (string)$userId, json_encode(['new_status' => $newStatus])]);

            Session::flash('success', "User account status changed to {$newStatus}.");
        }

        header('Location: /admin/users.php');
        exit;
    }

    /**
     * Admin Wallet Manual Adjustment
     */
    public function adjustWallet(): void
    {
        Auth::requireSuperAdmin();
        $adminId = (int)Auth::id();
        $userId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0.0);
        $type = (string)($_POST['type'] ?? 'credit');
        $reason = trim((string)($_POST['reason'] ?? 'Administrative correction'));

        $result = $this->walletService->adminAdjust($adminId, $userId, $amount, $type, $reason);
        if ($result['success']) {
            Session::flash('success', "Wallet adjusted successfully. New balance: \${$result['new_balance']}");
        } else {
            Session::flash('error', $result['message']);
        }

        header('Location: /admin/users.php');
        exit;
    }

    /**
     * Provider Management & Testing
     */
    public function providers(): void
    {
        Auth::requireSuperAdmin();

        $providers = $this->pdo->query("SELECT * FROM providers ORDER BY id ASC")->fetchAll();
        $recentLogs = $this->pdo->query("SELECT * FROM provider_logs ORDER BY created_at DESC LIMIT 20")->fetchAll();

        View::render('admin/providers', [
            'providers' => $providers,
            'recentLogs' => $recentLogs,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ], 'admin');
    }

    /**
     * Test Provider API Connection
     */
    public function testProvider(): void
    {
        Auth::requireSuperAdmin();
        header('Content-Type: application/json');

        $providerId = (int)($_POST['provider_id'] ?? 1);
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            echo json_encode(['success' => false, 'message' => 'Provider not found']);
            exit;
        }

        $p = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);
        $balance = $p->getBalance();

        if ($balance['success']) {
            $this->pdo->prepare("UPDATE providers SET balance = ?, last_sync_at = NOW() WHERE id = ?")->execute([$balance['balance'], $providerId]);
            echo json_encode(['success' => true, 'balance' => $balance['balance'], 'currency' => $balance['currency'], 'message' => 'Connection verified successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $balance['message']]);
        }
        exit;
    }

    /**
     * Trigger Country & Service Sync
     */
    public function syncNow(): void
    {
        Auth::requireSuperAdmin();
        header('Content-Type: application/json');

        $providerId = (int)($_POST['provider_id'] ?? 1);
        $type = $_POST['type'] ?? 'all';

        $cResult = $this->syncService->syncCountries($providerId);
        $sResult = $this->syncService->syncServices($providerId, 'in');

        echo json_encode([
            'success' => true,
            'message' => "Sync complete: {$cResult['message']} | {$sResult['message']}"
        ]);
        exit;
    }

    /**
     * Service Management & Custom Pricing
     */
    public function services(): void
    {
        Auth::requireSuperAdmin();

        $services = $this->pdo->query("
            SELECT s.*, 
                   COUNT(ps.id) as mapped_countries,
                   AVG(ps.provider_cost) as avg_cost
            FROM services s
            LEFT JOIN provider_services ps ON s.id = ps.service_id
            GROUP BY s.id
            ORDER BY s.service_name ASC
        ")->fetchAll();

        View::render('admin/services', ['services' => $services], 'admin');
    }

    /**
     * Update Service Custom Selling Price & Status
     */
    public function updateServicePrice(): void
    {
        Auth::requireSuperAdmin();
        $serviceId = (int)($_POST['service_id'] ?? 0);
        $price = (float)($_POST['custom_price'] ?? 1.0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $this->pdo->prepare("UPDATE services SET custom_price = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$price, $isActive, $serviceId]);

        Session::flash('success', 'Service updated successfully.');
        header('Location: /admin/services.php');
        exit;
    }

    /**
     * Order Monitoring
     */
    public function orders(): void
    {
        Auth::requireSuperAdmin();

        $orders = $this->pdo->query("
            SELECT o.*, u.email as user_email, s.service_name, c.country_name, p.name as provider_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN services s ON o.service_id = s.id
            JOIN countries c ON o.country_id = c.id
            JOIN providers p ON o.provider_id = p.id
            ORDER BY o.created_at DESC
            LIMIT 150
        ")->fetchAll();

        View::render('admin/orders', ['orders' => $orders], 'admin');
    }

    /**
     * Payment Queue & Gateway Settings
     */
    public function payments(): void
    {
        Auth::requireSuperAdmin();

        $manualPending = $this->pdo->query("
            SELECT m.*, u.name as user_name, u.email as user_email
            FROM manual_payment_requests m
            JOIN users u ON m.user_id = u.id
            WHERE m.status = 'PENDING'
            ORDER BY m.created_at ASC
        ")->fetchAll();

        $allPayments = $this->pdo->query("
            SELECT p.*, u.email as user_email
            FROM payments p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 50
        ")->fetchAll();

        $gateways = $this->pdo->query("SELECT * FROM payment_gateways ORDER BY id ASC")->fetchAll();

        View::render('admin/payments', [
            'manualPending' => $manualPending,
            'allPayments' => $allPayments,
            'gateways' => $gateways
        ], 'admin');
    }

    /**
     * Approve / Reject Manual Payment
     */
    public function reviewManualPayment(): void
    {
        Auth::requireSuperAdmin();
        $adminId = (int)Auth::id();
        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = (string)($_POST['action'] ?? 'approve');
        $notes = trim((string)($_POST['admin_notes'] ?? ''));

        $service = new ManualQrService($this->pdo);

        if ($action === 'approve') {
            $res = $service->approveRequest($requestId, $adminId, $notes);
        } else {
            $res = $service->rejectRequest($requestId, $adminId, $notes);
        }

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        header('Location: /admin/payments.php');
        exit;
    }
}

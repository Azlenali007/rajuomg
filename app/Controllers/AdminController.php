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

        // 7-day trend analytics for Chart.js
        $chartDates = [];
        $chartOrders = [];
        $chartRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStr = date('Y-m-d', strtotime("-$i days"));
            $chartDates[] = date('D, M d', strtotime($dayStr));
            $oCount = (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE substr(created_at, 1, 10) = '$dayStr'")->fetchColumn();
            $rSum = (float)$this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE substr(created_at, 1, 10) = '$dayStr' AND type = 'credit'")->fetchColumn();
            $chartOrders[] = $oCount;
            $chartRevenue[] = $rSum;
        }

        View::render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'provider' => $provider,
            'chartData' => [
                'labels' => $chartDates,
                'orders' => $chartOrders,
                'revenue' => $chartRevenue
            ]
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

        header('Location: /admin/users');
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

        header('Location: /admin/users');
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
     * Trigger Real Provider API Synchronization (Countries & Services)
     */
    public function syncNow(): void
    {
        Auth::requireSuperAdmin();
        header('Content-Type: application/json');

        $providerId = (int)($_POST['provider_id'] ?? 1);
        $countryCode = isset($_POST['country_code']) && trim((string)$_POST['country_code']) !== '' ? trim((string)$_POST['country_code']) : null;

        // 1. Sync countries directly from provider API
        $cResult = $this->syncService->syncCountries($providerId);

        // If provider authentication or connection fails, halt and report real error immediately
        if (!$cResult['success']) {
            echo json_encode([
                'success' => false,
                'imported_countries' => 0,
                'imported_services' => 0,
                'message' => 'Country sync failed: ' . $cResult['message']
            ]);
            exit;
        }

        // 2. Sync services for specified country or all active countries from provider API
        $sResult = $this->syncService->syncServices($providerId, $countryCode);

        $messages = [$cResult['message']];
        if ($sResult['success']) {
            $messages[] = $sResult['message'];
        } else {
            $messages[] = 'Service sync error: ' . $sResult['message'];
        }

        echo json_encode([
            'success' => $sResult['success'],
            'imported_countries' => $cResult['count'] ?? 0,
            'imported_services' => $sResult['count'] ?? 0,
            'message' => implode(' | ', $messages)
        ]);
        exit;
    }

    /**
     * Update Provider Configuration (API URL & Key)
     */
    public function updateProvider(): void
    {
        Auth::requireSuperAdmin();

        $providerId = (int)($_POST['provider_id'] ?? 1);
        $name = trim((string)($_POST['name'] ?? ''));
        $apiUrl = trim((string)($_POST['api_url'] ?? ''));
        $apiKey = trim((string)($_POST['api_key'] ?? ''));

        if (empty($name) || empty($apiUrl)) {
            Session::flash('error', 'Provider name and API URL are required.');
            header('Location: /admin/providers');
            exit;
        }

        if (!empty($apiKey)) {
            $stmt = $this->pdo->prepare("UPDATE providers SET name = ?, api_url = ?, api_key = ? WHERE id = ?");
            $stmt->execute([$name, $apiUrl, $apiKey, $providerId]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE providers SET name = ?, api_url = ? WHERE id = ?");
            $stmt->execute([$name, $apiUrl, $providerId]);
        }

        Session::flash('success', 'Provider settings updated successfully.');
        header('Location: /admin/providers');
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
                   COUNT(DISTINCT ps.id) as mapped_countries,
                   COALESCE(AVG(ps.provider_cost), 0) as avg_cost,
                   COUNT(DISTINCT o.id) as orders_count
            FROM services s
            LEFT JOIN provider_services ps ON s.id = ps.service_id
            LEFT JOIN orders o ON s.id = o.service_id
            GROUP BY s.id
            ORDER BY s.service_name ASC
        ")->fetchAll();

        $countries = $this->pdo->query("SELECT * FROM countries WHERE is_active = 1 ORDER BY country_name ASC")->fetchAll();
        $provider = $this->pdo->query("SELECT * FROM providers LIMIT 1")->fetch();

        View::render('admin/services', [
            'services' => $services,
            'countries' => $countries,
            'provider' => $provider,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ], 'admin');
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
        header('Location: /admin/services');
        exit;
    }

    /**
     * Bulk Delete Selected Services
     * Safely preserves historical orders and customer records
     */
    public function bulkDeleteServices(): void
    {
        Auth::requireSuperAdmin();
        $adminId = (int)Auth::id();

        // Support both POST array and JSON payload
        $rawIds = $_POST['service_ids'] ?? [];
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }

        $serviceIds = array_values(array_filter(array_map('intval', (array)$rawIds), fn($id) => $id > 0));

        if (empty($serviceIds)) {
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No services selected for deletion.']);
                exit;
            }
            Session::flash('error', 'No services selected for deletion.');
            header('Location: /admin/services');
            exit;
        }

        $deleted = 0;
        $deactivated = 0;

        $this->pdo->beginTransaction();
        try {
            $chkOrdersStmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE service_id = ?");
            $delMapStmt = $this->pdo->prepare("DELETE FROM provider_services WHERE service_id = ?");
            $delSrvStmt = $this->pdo->prepare("DELETE FROM services WHERE id = ?");
            $deactSrvStmt = $this->pdo->prepare("UPDATE services SET is_active = 0, updated_at = NOW() WHERE id = ?");

            foreach ($serviceIds as $id) {
                $chkOrdersStmt->execute([$id]);
                $orderCount = (int)$chkOrdersStmt->fetchColumn();

                if ($orderCount > 0) {
                    // Service is referenced by existing user orders: Preserve historical records!
                    $delMapStmt->execute([$id]);
                    $deactSrvStmt->execute([$id]);
                    $deactivated++;
                } else {
                    // No existing orders: Safe to delete completely
                    $delMapStmt->execute([$id]);
                    $delSrvStmt->execute([$id]);
                    $deleted++;
                }
            }

            // Audit log
            $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'BULK_DELETE_SERVICES', 'services', ?, ?, NOW())
            ")->execute([$adminId, implode(',', $serviceIds), json_encode(['deleted' => $deleted, 'deactivated' => $deactivated])]);

            $this->pdo->commit();

            $total = $deleted + $deactivated;
            $msg = "Successfully processed {$total} services: {$deleted} permanently deleted" . ($deactivated > 0 ? ", {$deactivated} deactivated to preserve historical order records." : ".");

            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'deleted' => $deleted, 'deactivated' => $deactivated, 'message' => $msg]);
                exit;
            }

            Session::flash('success', $msg);
            header('Location: /admin/services');
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Bulk deletion error: ' . $e->getMessage()]);
                exit;
            }
            Session::flash('error', 'Bulk deletion error: ' . $e->getMessage());
            header('Location: /admin/services');
            exit;
        }
    }

    /**
     * Delete All Services
     * Deliberate administrator action requiring explicit confirmation.
     * Safely preserves historical orders and customer records.
     */
    public function deleteAllServices(): void
    {
        Auth::requireSuperAdmin();
        $adminId = (int)Auth::id();

        $confirmation = trim((string)($_POST['confirm'] ?? ''));
        if ($confirmation !== 'DELETE_ALL' && $confirmation !== '1') {
            $errMsg = 'Deletion aborted: Explicit confirmation keyword required.';
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::flash('error', $errMsg);
            header('Location: /admin/services');
            exit;
        }

        $deleted = 0;
        $deactivated = 0;

        $this->pdo->beginTransaction();
        try {
            $allServiceIds = $this->pdo->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);

            $chkOrdersStmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE service_id = ?");
            $delMapStmt = $this->pdo->prepare("DELETE FROM provider_services WHERE service_id = ?");
            $delSrvStmt = $this->pdo->prepare("DELETE FROM services WHERE id = ?");
            $deactSrvStmt = $this->pdo->prepare("UPDATE services SET is_active = 0, updated_at = NOW() WHERE id = ?");

            foreach ($allServiceIds as $id) {
                $id = (int)$id;
                $chkOrdersStmt->execute([$id]);
                $orderCount = (int)$chkOrdersStmt->fetchColumn();

                if ($orderCount > 0) {
                    $delMapStmt->execute([$id]);
                    $deactSrvStmt->execute([$id]);
                    $deactivated++;
                } else {
                    $delMapStmt->execute([$id]);
                    $delSrvStmt->execute([$id]);
                    $deleted++;
                }
            }

            // Audit log
            $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'DELETE_ALL_SERVICES', 'services', 'ALL', ?, NOW())
            ")->execute([$adminId, json_encode(['deleted' => $deleted, 'deactivated' => $deactivated])]);

            $this->pdo->commit();

            $total = $deleted + $deactivated;
            $msg = "Delete All complete: {$deleted} services removed" . ($deactivated > 0 ? ", {$deactivated} services deactivated to preserve existing customer order history." : ".");

            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'deleted' => $deleted, 'deactivated' => $deactivated, 'message' => $msg]);
                exit;
            }

            Session::flash('success', $msg);
            header('Location: /admin/services');
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Delete All failed: ' . $e->getMessage()]);
                exit;
            }
            Session::flash('error', 'Delete All failed: ' . $e->getMessage());
            header('Location: /admin/services');
            exit;
        }
    }

    /**
     * Bulk Set Margin / Pricing on Selected or All Services
     */
    public function bulkSetMargin(): void
    {
        Auth::requireSuperAdmin();
        $adminId = (int)Auth::id();

        $scope = trim((string)($_POST['scope'] ?? 'selected')); // 'selected' or 'all'
        $marginType = trim((string)($_POST['margin_type'] ?? 'percentage')); // 'percentage' or 'fixed'
        $marginValue = (float)($_POST['margin_value'] ?? 0.0);

        if ($marginValue < 0) {
            $err = 'Margin value cannot be negative.';
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            Session::flash('error', $err);
            header('Location: /admin/services');
            exit;
        }

        $rawIds = $_POST['service_ids'] ?? [];
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $serviceIds = array_values(array_filter(array_map('intval', (array)$rawIds), fn($id) => $id > 0));

        if ($scope === 'selected' && empty($serviceIds)) {
            $err = 'No services selected for margin update.';
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            Session::flash('error', $err);
            header('Location: /admin/services');
            exit;
        }

        $this->pdo->beginTransaction();
        try {
            if ($scope === 'selected') {
                $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
                $stmt = $this->pdo->prepare("
                    SELECT s.id, s.service_name, s.custom_price, s.profit_margin_percent, COALESCE(AVG(ps.provider_cost), 0) as avg_cost 
                    FROM services s 
                    LEFT JOIN provider_services ps ON s.id = ps.service_id 
                    WHERE s.id IN ($placeholders) 
                    GROUP BY s.id
                ");
                $stmt->execute($serviceIds);
                $targets = $stmt->fetchAll();
            } else {
                $targets = $this->pdo->query("
                    SELECT s.id, s.service_name, s.custom_price, s.profit_margin_percent, COALESCE(AVG(ps.provider_cost), 0) as avg_cost 
                    FROM services s 
                    LEFT JOIN provider_services ps ON s.id = ps.service_id 
                    GROUP BY s.id
                ")->fetchAll();
            }

            $updateStmt = $this->pdo->prepare("UPDATE services SET custom_price = ?, profit_margin_percent = ?, updated_at = NOW() WHERE id = ?");
            $updated = 0;

            foreach ($targets as $srv) {
                $cost = (float)$srv['avg_cost'];
                if ($cost <= 0.0) {
                    $cost = (float)$srv['custom_price'];
                }

                if ($marginType === 'percentage') {
                    // Margin percent above provider cost
                    $newPrice = round(max(0.10, $cost * (1.0 + ($marginValue / 100.0))), 2);
                    $newMargin = $marginValue;
                } else {
                    // Fixed addition above provider cost
                    $newPrice = round(max(0.10, $cost + $marginValue), 2);
                    $newMargin = ($cost > 0 && $newPrice > 0) ? round((($newPrice - $cost) / $newPrice) * 100, 2) : 20.0;
                }

                $updateStmt->execute([$newPrice, $newMargin, $srv['id']]);
                $updated++;
            }

            // Audit log
            $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'BULK_SET_MARGIN', 'services', ?, ?, NOW())
            ")->execute([$adminId, $scope, json_encode(['margin_type' => $marginType, 'margin_value' => $marginValue, 'count' => $updated])]);

            $this->pdo->commit();

            $typeLabel = $marginType === 'percentage' ? "{$marginValue}%" : "\${$marginValue}";
            $msg = "Successfully applied {$typeLabel} margin to {$updated} services.";

            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'count' => $updated, 'message' => $msg]);
                exit;
            }

            Session::flash('success', $msg);
            header('Location: /admin/services');
            exit;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            if ($this->isJsonRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Bulk margin update failed: ' . $e->getMessage()]);
                exit;
            }
            Session::flash('error', 'Bulk margin update failed: ' . $e->getMessage());
            header('Location: /admin/services');
            exit;
        }
    }

    /**
     * Check if request expects JSON response
     */
    private function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $xReq = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json') || strtolower($xReq) === 'xmlhttprequest' || isset($_POST['format']) && $_POST['format'] === 'json';
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

        header('Location: /admin/payments');
        exit;
    }
}

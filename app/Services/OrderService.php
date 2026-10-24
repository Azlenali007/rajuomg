<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Modules\Providers\OtpDoctorProvider;
use PDO;
use Exception;

/**
 * OrderService
 * Manages OTP activation order lifecycle, provider dispatch, status polling, and automatic refunds.
 */
class OrderService
{
    protected PDO $pdo;
    protected WalletService $walletService;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->walletService = new WalletService($this->pdo);
    }

    /**
     * Create and activate a new number order
     */
    public function createOrder(int $userId, int $serviceId, ?int $countryId = null): array
    {
        // 1. Fetch service details
        $sStmt = $this->pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
        $sStmt->execute([$serviceId]);
        $service = $sStmt->fetch();

        if (!$service) {
            return ['success' => false, 'message' => 'Service is not active or unavailable.'];
        }

        // 2. Select country: default to India (id=1) or first active country if not specified
        if (!$countryId) {
            $cStmt = $this->pdo->prepare("SELECT id FROM countries WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
            $cStmt->execute();
            $country = $cStmt->fetch();
            $countryId = $country ? (int)$country['id'] : 1;
        }

        $cFetch = $this->pdo->prepare("SELECT * FROM countries WHERE id = ? AND is_active = 1");
        $cFetch->execute([$countryId]);
        $countryData = $cFetch->fetch();
        if (!$countryData) {
            return ['success' => false, 'message' => 'Selected country is not available.'];
        }

        // 3. Find active provider mapping
        $pStmt = $this->pdo->prepare("
            SELECT ps.*, p.api_url, p.api_key, p.id as p_id, p.code as p_code
            FROM provider_services ps
            JOIN providers p ON ps.provider_id = p.id
            WHERE ps.service_id = ? AND ps.country_id = ? AND p.is_active = 1
            LIMIT 1
        ");
        $pStmt->execute([$serviceId, $countryId]);
        $mapping = $pStmt->fetch();

        if (!$mapping) {
            // Look up primary active provider
            $fallbackP = $this->pdo->prepare("SELECT * FROM providers WHERE is_active = 1 LIMIT 1");
            $fallbackP->execute();
            $provider = $fallbackP->fetch();
            if (!$provider) {
                return ['success' => false, 'message' => 'No active SMS provider configured in system.'];
            }
            $chkService = $this->pdo->prepare("SELECT provider_service_code, provider_cost FROM provider_services WHERE service_id = ? AND provider_id = ? LIMIT 1");
            $chkService->execute([$serviceId, $provider['id']]);
            $existingServiceMap = $chkService->fetch();

            $providerServiceCode = $existingServiceMap ? $existingServiceMap['provider_service_code'] : $service['service_code'];
            $providerCost = $existingServiceMap ? (float)$existingServiceMap['provider_cost'] : round((float)$service['custom_price'] * 0.7, 4);
        } else {
            $provider = [
                'id' => $mapping['p_id'],
                'api_url' => $mapping['api_url'],
                'api_key' => $mapping['api_key'],
                'code' => $mapping['p_code']
            ];
            $providerServiceCode = !empty($mapping['provider_service_code']) ? $mapping['provider_service_code'] : $service['service_code'];
            $providerCost = (float)$mapping['provider_cost'];
        }

        $userPrice = (float)$service['custom_price'];
        $orderNumber = 'ORD-' . strtoupper(bin2hex(random_bytes(5)));

        // 4. Atomic wallet deduction before provider call
        $deduction = $this->walletService->deductForOrder($userId, $userPrice, $orderNumber);
        if (!$deduction['success']) {
            return ['success' => false, 'message' => $deduction['message']];
        }

        // 5. Call Provider API to acquire number using authentic provider identifiers
        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, (int)$provider['id']);
        $providerResponse = $providerInstance->getNumber($providerServiceCode, (string)$countryData['country_code']);

        if (!$providerResponse['success']) {
            // Provider failed: automatically refund wallet immediately
            $this->walletService->refundOrder($userId, $userPrice, $orderNumber, 'Provider allocation failed: ' . $providerResponse['message']);
            return [
                'success' => false,
                'message' => 'Failed to allocate phone number: ' . $providerResponse['message']
            ];
        }

        // 6. Save order into database
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $ins = $this->pdo->prepare("
            INSERT INTO orders (order_number, user_id, provider_id, service_id, country_id, phone_number, provider_activation_id, price, provider_cost, status, created_at, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW(), ?)
        ");
        $ins->execute([
            $orderNumber,
            $userId,
            $provider['id'],
            $serviceId,
            $countryId,
            $providerResponse['phone'],
            $providerResponse['activation_id'],
            $userPrice,
            $providerCost,
            $expiresAt
        ]);

        $orderId = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'phone_number' => $providerResponse['phone'],
            'service_name' => $service['service_name'],
            'country_name' => $countryData['country_name'],
            'price' => $userPrice,
            'expires_at' => $expiresAt,
            'message' => 'Phone number assigned successfully!'
        ];
    }

    /**
     * Poll order status from provider
     */
    public function checkStatus(int $orderId, ?int $userId = null): array
    {
        $query = "
            SELECT o.*, p.api_url, p.api_key, s.service_name, c.country_name
            FROM orders o
            JOIN providers p ON o.provider_id = p.id
            JOIN services s ON o.service_id = s.id
            JOIN countries c ON o.country_id = c.id
            WHERE o.id = ?
        ";
        $params = [$orderId];

        if ($userId !== null) {
            $query .= " AND o.user_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // If already completed or cancelled, return current state
        if (in_array($order['status'], ['COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED'], true)) {
            return [
                'success' => true,
                'status' => $order['status'],
                'otp' => $order['otp_code'],
                'full_sms' => $order['full_sms_text'],
                'phone_number' => $order['phone_number'],
                'time_remaining_seconds' => max(0, strtotime($order['expires_at']) - time())
            ];
        }

        // Check if expired
        if (time() >= strtotime($order['expires_at'])) {
            $this->expireOrder((int)$order['id']);
            return [
                'success' => true,
                'status' => 'EXPIRED',
                'otp' => null,
                'message' => 'Order expired with no SMS received. Amount has been refunded to your wallet.'
            ];
        }

        // Poll Provider API
        $providerInstance = new OtpDoctorProvider($order['api_url'], $order['api_key'], $this->pdo, (int)$order['provider_id']);
        $result = $providerInstance->getStatus($order['provider_activation_id']);

        if ($result['success'] && $result['status'] === 'RECEIVED' && !empty($result['otp'])) {
            // OTP Received! Update order state
            $upd = $this->pdo->prepare("
                UPDATE orders 
                SET status = 'RECEIVED', otp_code = ?, full_sms_text = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            $upd->execute([$result['otp'], $result['full_sms'], $order['id']]);

            return [
                'success' => true,
                'status' => 'RECEIVED',
                'otp' => $result['otp'],
                'full_sms' => $result['full_sms'],
                'phone_number' => $order['phone_number'],
                'message' => 'Verification code received!'
            ];
        }

        if ($result['success'] && $result['status'] === 'CANCELLED') {
            $this->cancelOrder((int)$order['id'], $userId, 'Provider reported cancellation');
            return [
                'success' => true,
                'status' => 'CANCELLED',
                'otp' => null,
                'message' => 'Order was cancelled and refunded.'
            ];
        }

        return [
            'success' => true,
            'status' => 'WAITING',
            'otp' => null,
            'phone_number' => $order['phone_number'],
            'time_remaining_seconds' => max(0, strtotime($order['expires_at']) - time()),
            'message' => 'Waiting for SMS...'
        ];
    }

    /**
     * Cancel an active order and issue automatic refund
     */
    public function cancelOrder(int $orderId, ?int $userId = null, string $reason = 'Cancelled by user'): array
    {
        $query = "
            SELECT o.*, p.api_url, p.api_key 
            FROM orders o 
            JOIN providers p ON o.provider_id = p.id 
            WHERE o.id = ?
        ";
        $params = [$orderId];

        if ($userId !== null) {
            $query .= " AND o.user_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        if ($order['status'] !== 'ACTIVE') {
            return ['success' => false, 'message' => "Order cannot be cancelled in state '{$order['status']}'."];
        }

        // Notify provider to release / cancel number (Status 8 = cancel)
        $providerInstance = new OtpDoctorProvider($order['api_url'], $order['api_key'], $this->pdo, (int)$order['provider_id']);
        $providerInstance->setStatus($order['provider_activation_id'], 8);

        // Update local order status
        $upd = $this->pdo->prepare("UPDATE orders SET status = 'CANCELLED', cancelled_at = NOW() WHERE id = ?");
        $upd->execute([$orderId]);

        // Issue full refund to user wallet
        $refundResult = $this->walletService->refundOrder(
            (int)$order['user_id'],
            (float)$order['price'],
            $order['order_number'],
            $reason
        );

        return [
            'success' => true,
            'message' => 'Order cancelled successfully. Full amount has been refunded to your wallet.',
            'new_balance' => $refundResult['new_balance'] ?? null
        ];
    }

    /**
     * Expire orders whose 15-minute countdown elapsed with no OTP
     */
    public function expireOrder(int $orderId): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ? AND status = 'ACTIVE'");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return false;
        }

        $upd = $this->pdo->prepare("UPDATE orders SET status = 'EXPIRED', cancelled_at = NOW() WHERE id = ?");
        $upd->execute([$orderId]);

        $this->walletService->refundOrder(
            (int)$order['user_id'],
            (float)$order['price'],
            $order['order_number'],
            'Order timeout - 15 minute expiration'
        );

        return true;
    }
}

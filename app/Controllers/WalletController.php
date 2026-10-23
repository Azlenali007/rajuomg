<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Session;
use App\Core\View;
use App\Modules\Payments\ManualQrService;
use App\Modules\Payments\RazorpayService;
use App\Services\WalletService;
use PDO;
use Exception;

class WalletController
{
    protected PDO $pdo;
    protected WalletService $walletService;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->walletService = new WalletService($this->pdo);
    }

    /**
     * Show wallet top-up & ledger transactions
     */
    public function showWallet(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();

        $wallet = $this->walletService->getWallet($userId);

        // Fetch enabled payment gateways
        $gStmt = $this->pdo->query("SELECT * FROM payment_gateways WHERE is_enabled = 1");
        $gateways = $gStmt->fetchAll();

        $razorpay = null;
        $manualQr = null;

        foreach ($gateways as $g) {
            $conf = json_decode($g['config_json'] ?? '{}', true);
            if ($g['code'] === 'razorpay') {
                $razorpay = $conf;
            } elseif ($g['code'] === 'manual_qr') {
                $manualQr = $conf;
            }
        }

        // Fetch recent ledger transactions
        $txStmt = $this->pdo->prepare("
            SELECT * FROM wallet_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $txStmt->execute([$userId]);
        $transactions = $txStmt->fetchAll();

        // Fetch manual requests
        $mStmt = $this->pdo->prepare("
            SELECT * FROM manual_payment_requests 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $mStmt->execute([$userId]);
        $manualRequests = $mStmt->fetchAll();

        View::render('user/wallet', [
            'wallet' => $wallet,
            'razorpay' => $razorpay,
            'manualQr' => $manualQr,
            'transactions' => $transactions,
            'manualRequests' => $manualRequests,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ], 'user');
    }

    /**
     * Initiate Razorpay Payment Intent
     */
    public function initRazorpay(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = (int)Auth::id();
        $amount = (float)($_POST['amount'] ?? 0.0);

        if ($amount < 1.0) {
            echo json_encode(['success' => false, 'message' => 'Minimum top-up amount is $1.00']);
            exit;
        }

        // Check if gateway enabled
        $stmt = $this->pdo->prepare("SELECT config_json FROM payment_gateways WHERE code = 'razorpay' AND is_enabled = 1");
        $stmt->execute();
        $gateway = $stmt->fetch();

        if (!$gateway) {
            echo json_encode(['success' => false, 'message' => 'Razorpay payment gateway is currently disabled.']);
            exit;
        }

        $config = json_decode($gateway['config_json'], true);
        $rzp = new RazorpayService($config['key_id'], $config['key_secret'], $config['webhook_secret'] ?? null);

        $ref = 'PAY_' . strtoupper(bin2hex(random_bytes(6)));

        // Insert pending payment record
        $ins = $this->pdo->prepare("
            INSERT INTO payments (payment_reference, user_id, gateway_code, amount, currency, status, created_at)
            VALUES (?, ?, 'razorpay', ?, 'USD', 'PENDING', NOW())
        ");
        $ins->execute([$ref, $userId, $amount]);

        $orderResult = $rzp->createOrder($amount, 'USD', $ref, [
            'user_id' => $userId,
            'name' => Auth::user()['name'],
            'email' => Auth::user()['email']
        ]);

        if (!$orderResult['success']) {
            echo json_encode(['success' => false, 'message' => $orderResult['message']]);
            exit;
        }

        // Update payment with gateway order id
        $upd = $this->pdo->prepare("UPDATE payments SET gateway_order_id = ? WHERE payment_reference = ?");
        $upd->execute([$orderResult['order_id'], $ref]);

        echo json_encode([
            'success' => true,
            'checkout_data' => $orderResult['checkout_data'],
            'payment_reference' => $ref
        ]);
        exit;
    }

    /**
     * Verify Razorpay Client Return & Signature
     */
    public function verifyRazorpay(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = (int)Auth::id();
        $ref = trim((string)($_POST['payment_reference'] ?? ''));
        $orderId = trim((string)($_POST['razorpay_order_id'] ?? ''));
        $paymentId = trim((string)($_POST['razorpay_payment_id'] ?? ''));
        $signature = trim((string)($_POST['razorpay_signature'] ?? ''));

        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE payment_reference = ? AND user_id = ?");
        $stmt->execute([$ref, $userId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'Payment reference not found.']);
            exit;
        }

        if ($payment['status'] === 'SUCCESS') {
            echo json_encode(['success' => true, 'message' => 'Payment already processed and credited.']);
            exit;
        }

        // Fetch gateway secret
        $gStmt = $this->pdo->prepare("SELECT config_json FROM payment_gateways WHERE code = 'razorpay' AND is_enabled = 1");
        $gStmt->execute();
        $gateway = $gStmt->fetch();
        $config = json_decode($gateway['config_json'], true);

        $rzp = new RazorpayService($config['key_id'], $config['key_secret']);
        $isValid = $rzp->verifyPayment([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature
        ]);

        if (!$isValid) {
            $this->pdo->prepare("UPDATE payments SET status = 'FAILED' WHERE id = ?")->execute([$payment['id']]);
            echo json_encode(['success' => false, 'message' => 'Invalid payment signature. Verification rejected.']);
            exit;
        }

        // Atomic credit to wallet
        $this->creditPayment((int)$payment['id'], $paymentId, $signature);

        echo json_encode(['success' => true, 'message' => 'Payment verified! Wallet has been credited successfully.']);
        exit;
    }

    /**
     * Submit Manual QR Payment Proof
     */
    public function submitManual(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();

        $amount = (float)($_POST['amount'] ?? 0.0);
        $reference = trim((string)($_POST['transaction_reference'] ?? ''));

        $service = new ManualQrService($this->pdo);
        $result = $service->submitRequest($userId, $amount, $reference, null);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        header('Location: /wallet.php');
        exit;
    }

    /**
     * Credit payment to user wallet atomically
     */
    protected function creditPayment(int $paymentId, string $gatewayPaymentId, string $signature): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ? FOR UPDATE");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();

            if ($payment['status'] === 'SUCCESS') {
                $this->pdo->rollBack();
                return; // Prevent duplicate credit
            }

            $userId = (int)$payment['user_id'];
            $amount = (float)$payment['amount'];

            // Update payment record
            $upd = $this->pdo->prepare("
                UPDATE payments 
                SET status = 'SUCCESS', gateway_payment_id = ?, gateway_signature = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $upd->execute([$gatewayPaymentId, $signature, $paymentId]);

            // Update user wallet
            $wStmt = $this->pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $wStmt->execute([$userId]);
            $wallet = $wStmt->fetch();

            $newBalance = (float)$wallet['balance'] + $amount;
            $this->pdo->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ?")->execute([$newBalance, $wallet['id']]);

            // Ledger record
            $tx = $this->pdo->prepare("
                INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, reference_type, reference_id, description, status, created_at)
                VALUES (?, ?, 'credit', ?, ?, 'payment', ?, ?, 'completed', NOW())
            ");
            $tx->execute([
                $wallet['id'],
                $userId,
                $amount,
                $newBalance,
                $payment['payment_reference'],
                "Razorpay wallet recharge #{$gatewayPaymentId}"
            ]);

            $this->pdo->commit();
            Logger::info("Payment credited to wallet", ['payment_id' => $paymentId, 'user_id' => $userId, 'amount' => $amount]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Failed to credit payment: " . $e->getMessage(), ['payment_id' => $paymentId]);
        }
    }
}

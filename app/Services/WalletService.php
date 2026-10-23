<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;
use Exception;

/**
 * WalletService
 * Strictly manages financial ledger, atomic balance updates, credits, debits, and refunds.
 */
class WalletService
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Get or create wallet for user
     */
    public function getWallet(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
        $stmt->execute([$userId]);
        $wallet = $stmt->fetch();

        if (!$wallet) {
            $stmt = $this->pdo->prepare("INSERT INTO wallets (user_id, balance, currency, created_at) VALUES (?, 0.0000, 'USD', NOW())");
            $stmt->execute([$userId]);
            $walletId = (int)$this->pdo->lastInsertId();
            return [
                'id' => $walletId,
                'user_id' => $userId,
                'balance' => '0.0000',
                'locked_balance' => '0.0000',
                'currency' => 'USD'
            ];
        }

        return $wallet;
    }

    /**
     * Atomically deduct funds for order purchase
     */
    public function deductForOrder(int $userId, float $amount, string $orderNumber): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid transaction amount'];
        }

        $this->pdo->beginTransaction();
        try {
            // Lock wallet row
            $stmt = $this->pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Wallet not initialized'];
            }

            $currentBalance = (float)$wallet['balance'];
            if ($currentBalance < $amount) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => sprintf('Insufficient wallet balance. Required: $%.2f, Available: $%.2f', $amount, $currentBalance)
                ];
            }

            $walletId = (int)$wallet['id'];
            $newBalance = $currentBalance - $amount;

            // Atomic update
            $upd = $this->pdo->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ? AND balance >= ?");
            $upd->execute([$newBalance, $walletId, $amount]);

            if ($upd->rowCount() === 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Concurrent transaction conflict. Please retry.'];
            }

            // Ledger entry
            $tx = $this->pdo->prepare("
                INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, reference_type, reference_id, description, status, created_at)
                VALUES (?, ?, 'debit', ?, ?, 'order', ?, ?, 'completed', NOW())
            ");
            $tx->execute([
                $walletId,
                $userId,
                $amount,
                $newBalance,
                $orderNumber,
                "Order purchase #{$orderNumber}"
            ]);

            $this->pdo->commit();
            Logger::info("Wallet deducted for order", ['user_id' => $userId, 'amount' => $amount, 'order' => $orderNumber]);

            return ['success' => true, 'new_balance' => $newBalance];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Wallet deduction failed: " . $e->getMessage(), ['user_id' => $userId, 'order' => $orderNumber]);
            return ['success' => false, 'message' => 'Database error during wallet deduction.'];
        }
    }

    /**
     * Refund order amount back to user wallet (e.g. cancelled or expired with no OTP received)
     */
    public function refundOrder(int $userId, float $amount, string $orderNumber, string $reason = 'Order cancelled / no SMS'): array
    {
        $this->pdo->beginTransaction();
        try {
            // Check if refund already applied to avoid double crediting
            $chk = $this->pdo->prepare("SELECT id FROM wallet_transactions WHERE reference_type = 'refund' AND reference_id = ?");
            $chk->execute([$orderNumber]);
            if ($chk->fetch()) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Refund has already been credited for this order.'];
            }

            $stmt = $this->pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Wallet not found'];
            }

            $walletId = (int)$wallet['id'];
            $currentBalance = (float)$wallet['balance'];
            $newBalance = $currentBalance + $amount;

            $upd = $this->pdo->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ?");
            $upd->execute([$newBalance, $walletId]);

            $tx = $this->pdo->prepare("
                INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, reference_type, reference_id, description, status, created_at)
                VALUES (?, ?, 'refund', ?, ?, 'refund', ?, ?, 'completed', NOW())
            ");
            $tx->execute([
                $walletId,
                $userId,
                $amount,
                $newBalance,
                $orderNumber,
                "Refund for order #{$orderNumber}: {$reason}"
            ]);

            $this->pdo->commit();
            Logger::info("Order refunded successfully", ['user_id' => $userId, 'amount' => $amount, 'order' => $orderNumber]);
            return ['success' => true, 'new_balance' => $newBalance];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Refund error: " . $e->getMessage(), ['order' => $orderNumber]);
            return ['success' => false, 'message' => 'Refund processing error: ' . $e->getMessage()];
        }
    }

    /**
     * Admin manual balance adjustment with audit logging
     */
    public function adminAdjust(int $adminId, int $userId, float $amount, string $type, string $reason): array
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();

            if (!$wallet) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'User wallet does not exist.'];
            }

            $walletId = (int)$wallet['id'];
            $currentBalance = (float)$wallet['balance'];

            if ($type === 'credit') {
                $newBalance = $currentBalance + $amount;
            } else {
                if ($currentBalance < $amount) {
                    $this->pdo->rollBack();
                    return ['success' => false, 'message' => 'Cannot debit more than user current balance.'];
                }
                $newBalance = $currentBalance - $amount;
            }

            $upd = $this->pdo->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ?");
            $upd->execute([$newBalance, $walletId]);

            $refId = 'ADJ_' . strtoupper(bin2hex(random_bytes(6)));

            $tx = $this->pdo->prepare("
                INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, reference_type, reference_id, description, status, created_at)
                VALUES (?, ?, 'admin_adjustment', ?, ?, 'manual_adjustment', ?, ?, 'completed', NOW())
            ");
            $tx->execute([
                $walletId,
                $userId,
                $amount,
                $newBalance,
                $refId,
                "Admin adjustment: {$reason}"
            ]);

            $audit = $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'WALLET_ADJUSTMENT', 'wallets', ?, ?, NOW())
            ");
            $audit->execute([
                $adminId,
                (string)$walletId,
                json_encode(['user_id' => $userId, 'adjustment_type' => $type, 'amount' => $amount, 'reason' => $reason])
            ]);

            $this->pdo->commit();
            return ['success' => true, 'new_balance' => $newBalance, 'reference' => $refId];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Database failure: ' . $e->getMessage()];
        }
    }
}

<?php
declare(strict_types=1);

namespace App\Modules\Payments;

use PDO;
use Exception;

/**
 * ManualQrService
 * Manages manual UPI/Crypto/Bank QR top-up requests and administrator review workflows
 */
class ManualQrService
{
    protected PDO $pdo;
    protected array $config;

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * Submit a manual payment proof request by user
     */
    public function submitRequest(int $userId, float $amount, string $referenceId, ?string $proofFilePath = null): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }

        $referenceId = trim(strip_tags($referenceId));
        if (strlen($referenceId) < 4) {
            return ['success' => false, 'message' => 'Please provide a valid transaction reference / UTR / TxHash.'];
        }

        // Check if reference already submitted
        $stmt = $this->pdo->prepare("SELECT id FROM manual_payment_requests WHERE transaction_reference = ? LIMIT 1");
        $stmt->execute([$referenceId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'This transaction reference has already been submitted. Duplicate submissions are rejected.'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO manual_payment_requests (user_id, amount, transaction_reference, payment_proof_file, status, created_at)
            VALUES (?, ?, ?, ?, 'PENDING', NOW())
        ");
        $stmt->execute([$userId, $amount, $referenceId, $proofFilePath]);

        return [
            'success' => true,
            'request_id' => (int)$this->pdo->lastInsertId(),
            'message' => 'Manual payment request submitted successfully! It is now pending administrator verification.'
        ];
    }

    /**
     * Approve a manual payment request (Super Admin only)
     * Executes atomic wallet credit with audit trail
     */
    public function approveRequest(int $requestId, int $adminUserId, string $notes = ''): array
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM manual_payment_requests WHERE id = ? FOR UPDATE");
            $stmt->execute([$requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Payment request not found.'];
            }

            if ($request['status'] !== 'PENDING') {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => "Request is already {$request['status']}. Cannot process again."];
            }

            $userId = (int)$request['user_id'];
            $amount = (float)$request['amount'];
            $ref = $request['transaction_reference'];

            // 1. Update request status
            $upd = $this->pdo->prepare("
                UPDATE manual_payment_requests 
                SET status = 'APPROVED', admin_notes = ?, reviewed_by_user_id = ?, reviewed_at = NOW() 
                WHERE id = ?
            ");
            $upd->execute([$notes, $adminUserId, $requestId]);

            // 2. Fetch or create user wallet
            $wStmt = $this->pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $wStmt->execute([$userId]);
            $wallet = $wStmt->fetch();

            if (!$wallet) {
                $wIns = $this->pdo->prepare("INSERT INTO wallets (user_id, balance, currency, created_at) VALUES (?, 0.0000, 'USD', NOW())");
                $wIns->execute([$userId]);
                $walletId = (int)$this->pdo->lastInsertId();
                $currentBalance = 0.0;
            } else {
                $walletId = (int)$wallet['id'];
                $currentBalance = (float)$wallet['balance'];
            }

            $newBalance = $currentBalance + $amount;

            // 3. Update wallet balance atomically
            $updWallet = $this->pdo->prepare("UPDATE wallets SET balance = ?, updated_at = NOW() WHERE id = ?");
            $updWallet->execute([$newBalance, $walletId]);

            // 4. Insert immutable transaction ledger
            $txStmt = $this->pdo->prepare("
                INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, balance_after, reference_type, reference_id, description, status, created_at)
                VALUES (?, ?, 'credit', ?, ?, 'payment', ?, ?, 'completed', NOW())
            ");
            $txStmt->execute([
                $walletId,
                $userId,
                $amount,
                $newBalance,
                'MANUAL_QR_' . $requestId,
                "Manual QR Top-up Approved (Ref: {$ref})"
            ]);

            // 5. Audit log
            $audit = $this->pdo->prepare("
                INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
                VALUES (?, 'APPROVE_MANUAL_PAYMENT', 'manual_payment_requests', ?, ?, NOW())
            ");
            $audit->execute([
                $adminUserId,
                (string)$requestId,
                json_encode(['user_id' => $userId, 'amount' => $amount, 'reference' => $ref, 'notes' => $notes])
            ]);

            $this->pdo->commit();
            return ['success' => true, 'message' => "Payment #{$requestId} of {$amount} approved and credited to user."];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Transaction failure: ' . $e->getMessage()];
        }
    }

    /**
     * Reject a manual payment request (Super Admin only)
     */
    public function rejectRequest(int $requestId, int $adminUserId, string $notes = ''): array
    {
        $stmt = $this->pdo->prepare("
            UPDATE manual_payment_requests 
            SET status = 'REJECTED', admin_notes = ?, reviewed_by_user_id = ?, reviewed_at = NOW() 
            WHERE id = ? AND status = 'PENDING'
        ");
        $stmt->execute([$notes, $adminUserId, $requestId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Request not found or already processed.'];
        }

        // Audit log
        $audit = $this->pdo->prepare("
            INSERT INTO admin_audit_logs (admin_id, action, target_type, target_id, details_json, created_at)
            VALUES (?, 'REJECT_MANUAL_PAYMENT', 'manual_payment_requests', ?, ?, NOW())
        ");
        $audit->execute([
            $adminUserId,
            (string)$requestId,
            json_encode(['notes' => $notes])
        ]);

        return ['success' => true, 'message' => "Payment #{$requestId} rejected."];
    }
}

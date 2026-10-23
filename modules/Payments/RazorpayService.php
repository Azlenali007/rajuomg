<?php
declare(strict_types=1);

namespace App\Modules\Payments;

/**
 * RazorpayService
 * Native PHP implementation of Razorpay API with strict HMAC-SHA256 signature validation
 */
class RazorpayService implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;
    protected ?string $webhookSecret;
    protected string $apiUrl = 'https://api.razorpay.com/v1';

    public function __construct(string $keyId, string $keySecret, ?string $webhookSecret = null)
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->webhookSecret = $webhookSecret;
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder(float $amount, string $currency, string $referenceId, array $customerDetails = []): array
    {
        // Razorpay takes amount in subunits (e.g., paise / cents)
        $amountSubunits = (int)round($amount * 100);

        $payload = [
            'amount' => $amountSubunits,
            'currency' => strtoupper($currency),
            'receipt' => $referenceId,
            'payment_capture' => 1,
            'notes' => [
                'user_id' => $customerDetails['user_id'] ?? 'unknown',
                'reference' => $referenceId
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . '/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return [
                'success' => false,
                'order_id' => '',
                'checkout_data' => [],
                'message' => 'Razorpay API error: ' . ($error ?: $response)
            ];
        }

        $data = json_decode((string)$response, true);
        $orderId = $data['id'] ?? '';

        return [
            'success' => true,
            'order_id' => $orderId,
            'checkout_data' => [
                'key' => $this->keyId,
                'amount' => $amountSubunits,
                'currency' => strtoupper($currency),
                'name' => 'OTP Doctor',
                'description' => 'Wallet Balance Top-up',
                'order_id' => $orderId,
                'prefill' => [
                    'name' => $customerDetails['name'] ?? '',
                    'email' => $customerDetails['email'] ?? ''
                ],
                'theme' => [
                    'color' => '#7c3aed' // Premium purple accent
                ]
            ],
            'message' => 'Order created'
        ];
    }

    /**
     * Verify client payment signature
     * signature = hmac_sha256(order_id + "|" + payment_id, secret)
     */
    public function verifyPayment(array $paymentData): bool
    {
        $orderId = $paymentData['razorpay_order_id'] ?? '';
        $paymentId = $paymentData['razorpay_payment_id'] ?? '';
        $receivedSignature = $paymentData['razorpay_signature'] ?? '';

        if (empty($orderId) || empty($paymentId) || empty($receivedSignature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Verify incoming webhook payload
     */
    public function handleWebhook(string $payloadRaw, string $signatureHeader): array
    {
        if (empty($this->webhookSecret)) {
            return ['verified' => false, 'event_id' => '', 'event_type' => '', 'payment_id' => null, 'status' => 'NO_SECRET'];
        }

        $expectedSignature = hash_hmac('sha256', $payloadRaw, $this->webhookSecret);
        if (!hash_equals($expectedSignature, $signatureHeader)) {
            return ['verified' => false, 'event_id' => '', 'event_type' => '', 'payment_id' => null, 'status' => 'INVALID_SIGNATURE'];
        }

        $json = json_decode($payloadRaw, true);
        $eventId = $json['event_id'] ?? ($json['id'] ?? uniqid('evt_'));
        $eventType = $json['event'] ?? 'unknown';
        $payment = $json['payload']['payment']['entity'] ?? [];
        $paymentId = $payment['id'] ?? null;
        $status = $payment['status'] ?? 'unknown';

        return [
            'verified' => true,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payment_id' => $paymentId,
            'status' => $status,
            'order_id' => $payment['order_id'] ?? null,
            'amount' => isset($payment['amount']) ? ((float)$payment['amount'] / 100) : 0.0,
            'currency' => $payment['currency'] ?? 'USD'
        ];
    }
}

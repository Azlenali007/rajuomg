<?php
declare(strict_types=1);

namespace App\Modules\Payments;

interface PaymentGatewayInterface
{
    /**
     * Create an initiation order / intent
     * @param float $amount
     * @param string $currency
     * @param string $referenceId
     * @param array $customerDetails
     * @return array ['success' => bool, 'order_id' => string, 'checkout_data' => array, 'message' => string]
     */
    public function createOrder(float $amount, string $currency, string $referenceId, array $customerDetails = []): array;

    /**
     * Verify payment signature / payload from gateway return
     * @param array $paymentData
     * @return bool
     */
    public function verifyPayment(array $paymentData): bool;

    /**
     * Verify and process incoming webhook event
     * @param string $payloadRaw
     * @param string $signatureHeader
     * @return array ['verified' => bool, 'event_id' => string, 'event_type' => string, 'payment_id' => ?string, 'status' => string]
     */
    public function handleWebhook(string $payloadRaw, string $signatureHeader): array;
}

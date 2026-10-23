<?php
declare(strict_types=1);

namespace App\Modules\Providers;

/**
 * Interface ProviderInterface
 * Contract for SMS OTP Verification Providers
 */
interface ProviderInterface
{
    /**
     * Get provider account balance
     * @return array ['success' => bool, 'balance' => float, 'currency' => string, 'raw' => mixed]
     */
    public function getBalance(): array;

    /**
     * Fetch list of available countries
     * @return array ['success' => bool, 'countries' => array, 'message' => string]
     */
    public function getCountries(): array;

    /**
     * Fetch list of available services and prices for a country
     * @param string $countryCode
     * @return array ['success' => bool, 'services' => array, 'message' => string]
     */
    public function getServices(string $countryCode): array;

    /**
     * Request/order a phone number for a service
     * @param string $serviceCode
     * @param string $countryCode
     * @param array $options
     * @return array ['success' => bool, 'activation_id' => string, 'phone' => string, 'cost' => float, 'message' => string]
     */
    public function getNumber(string $serviceCode, string $countryCode, array $options = []): array;

    /**
     * Retrieve activation status and SMS OTP code
     * @param string $activationId
     * @return array ['success' => bool, 'status' => string, 'otp' => ?string, 'full_sms' => ?string, 'raw' => string]
     */
    public function getStatus(string $activationId): array;

    /**
     * Set activation status (cancel, complete, or request second SMS)
     * @param string $activationId
     * @param int $statusCode (e.g., 8 = cancel, 6 = complete/finish, 1 = ready)
     * @return array ['success' => bool, 'response' => string, 'message' => string]
     */
    public function setStatus(string $activationId, int $statusCode): array;
}

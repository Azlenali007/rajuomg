<?php
declare(strict_types=1);

namespace App\Modules\Providers;

use PDO;
use Exception;

/**
 * OtpDoctorProvider
 * External SMS Provider implementation for https://www.otpdoctor.in/stubs/handler_api.php
 */
class OtpDoctorProvider implements ProviderInterface
{
    protected string $apiUrl;
    protected string $apiKey;
    protected ?PDO $pdo;
    protected int $providerId;

    public function __construct(string $apiUrl, string $apiKey, ?PDO $pdo = null, int $providerId = 1)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->pdo = $pdo;
        $this->providerId = $providerId;
    }

    /**
     * Map provider error codes to descriptive human-readable explanations
     */
    protected function parseApiError(string $body): ?string
    {
        $body = trim($body);
        if ($body === 'BAD_KEY') {
            return 'Provider rejected the API Key (BAD_KEY). Please update the API key in Super Admin > Provider API settings.';
        }
        if ($body === 'NO_KEY') {
            return 'Provider API Key is missing (NO_KEY).';
        }
        if ($body === 'BAD_ACTION') {
            return 'Provider rejected the action command (BAD_ACTION).';
        }
        if ($body === 'BAD_SERVICE') {
            return 'Requested service code is not supported by the provider (BAD_SERVICE).';
        }
        if ($body === 'BAD_COUNTRY') {
            return 'Requested country code is not supported by the provider (BAD_COUNTRY).';
        }
        if ($body === 'ERROR_SQL') {
            return 'Provider upstream database error (ERROR_SQL).';
        }
        if (str_starts_with($body, 'NO_NUMBERS')) {
            return 'No virtual numbers currently available from provider for this service.';
        }
        if (str_starts_with($body, 'NO_BALANCE')) {
            return 'Provider account balance is depleted (NO_BALANCE). Please top-up provider account.';
        }
        return null;
    }

    /**
     * Retrieve provider balance
     */
    public function getBalance(): array
    {
        $response = $this->sendRequest(['action' => 'getBalance']);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'balance' => 0.0,
                'currency' => 'USD',
                'message' => $response['error'] ?? 'Failed to reach OTP provider endpoint'
            ];
        }

        $body = trim((string)($response['body'] ?? ''));
        $apiErr = $this->parseApiError($body);
        if ($apiErr) {
            return [
                'success' => false,
                'balance' => 0.0,
                'currency' => 'USD',
                'message' => $apiErr
            ];
        }

        // Response format is usually: ACCESS_BALANCE:145.20 or JSON
        if (str_starts_with($body, 'ACCESS_BALANCE:')) {
            $parts = explode(':', $body);
            $balance = (float)($parts[1] ?? 0.0);
            return [
                'success' => true,
                'balance' => $balance,
                'currency' => 'USD',
                'raw' => $body
            ];
        }

        // Try JSON
        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json['balance'])) {
            return [
                'success' => true,
                'balance' => (float)$json['balance'],
                'currency' => $json['currency'] ?? 'USD',
                'raw' => $json
            ];
        }

        return [
            'success' => false,
            'balance' => 0.0,
            'currency' => 'USD',
            'message' => "Unexpected provider response: " . substr($body, 0, 80)
        ];
    }

    /**
     * Retrieve available countries (Active and validated only)
     */
    public function getCountries(): array
    {
        $response = $this->sendRequest(['action' => 'getCountries']);
        if (!$response['success']) {
            return ['success' => false, 'countries' => [], 'message' => $response['error'] ?? 'API connection error'];
        }

        $body = trim((string)($response['body'] ?? ''));
        $apiErr = $this->parseApiError($body);
        if ($apiErr) {
            return ['success' => false, 'countries' => [], 'message' => $apiErr];
        }

        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $countries = [];
            foreach ($json as $key => $item) {
                if (!is_array($item)) continue;

                // Explicit active / visibility check according to provider status rules
                if (isset($item['visible']) && ($item['visible'] === 0 || $item['visible'] === '0' || $item['visible'] === false)) continue;
                if (isset($item['status']) && in_array(strtolower((string)$item['status']), ['inactive', 'disabled', 'unavailable', '0', 'false'])) continue;
                if (isset($item['enabled']) && ($item['enabled'] === 0 || $item['enabled'] === '0' || $item['enabled'] === false)) continue;
                if (isset($item['is_active']) && ($item['is_active'] === 0 || $item['is_active'] === '0' || $item['is_active'] === false)) continue;

                $code = strtolower(trim((string)($item['code'] ?? $item['iso'] ?? $item['id'] ?? $key)));
                $name = trim((string)($item['name'] ?? $item['eng'] ?? $item['rus'] ?? ''));
                if (empty($name)) {
                    $name = 'Country ' . strtoupper($code);
                }
                $dial = isset($item['prefix']) ? '+' . ltrim((string)$item['prefix'], '+') : null;

                // Validate required fields
                if (empty($code) || empty($name)) {
                    continue;
                }

                $countries[] = [
                    'code' => $code,
                    'name' => $name,
                    'dial_code' => $dial,
                    'is_active' => 1
                ];
            }
            return ['success' => true, 'countries' => $countries, 'message' => 'Countries loaded'];
        }

        return ['success' => false, 'countries' => [], 'message' => 'Unable to parse countries from provider: ' . substr($body, 0, 80)];
    }

    /**
     * Retrieve services for a country (Active and available services only)
     */
    public function getServices(string $countryCode): array
    {
        $response = $this->sendRequest([
            'action' => 'getServices',
            'country' => $countryCode
        ]);

        if (!$response['success']) {
            return ['success' => false, 'services' => [], 'message' => $response['error'] ?? 'API connection error'];
        }

        $body = trim((string)($response['body'] ?? ''));
        $apiErr = $this->parseApiError($body);
        if ($apiErr) {
            return ['success' => false, 'services' => [], 'message' => $apiErr];
        }

        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $serviceItems = $json;
            // Handle nested country object responses (e.g. {"in": {...}} or {"0": {...}})
            if (isset($json[$countryCode]) && is_array($json[$countryCode])) {
                $serviceItems = $json[$countryCode];
            } elseif (count($json) === 1 && is_array(reset($json))) {
                $first = reset($json);
                if (is_array($first) && !isset($first['cost']) && !isset($first['price'])) {
                    $serviceItems = $first;
                }
            }

            $services = [];
            foreach ($serviceItems as $code => $data) {
                $serviceCode = strtolower(trim((string)$code));
                if (empty($serviceCode)) continue;

                // Filter out inactive / disabled / discontinued services based on provider fields
                $isActive = true;
                if (is_array($data)) {
                    if (isset($data['status']) && in_array(strtolower((string)$data['status']), ['inactive', 'disabled', 'discontinued', 'unavailable', '0', 'false'])) {
                        $isActive = false;
                    }
                    if (isset($data['visible']) && ($data['visible'] === 0 || $data['visible'] === '0' || $data['visible'] === false)) {
                        $isActive = false;
                    }
                    if (isset($data['enabled']) && ($data['enabled'] === 0 || $data['enabled'] === '0' || $data['enabled'] === false)) {
                        $isActive = false;
                    }
                    if (isset($data['is_active']) && ($data['is_active'] === 0 || $data['is_active'] === '0' || $data['is_active'] === false)) {
                        $isActive = false;
                    }
                }

                if (!$isActive) {
                    continue;
                }

                $name = is_array($data) ? ($data['name'] ?? strtoupper($serviceCode)) : strtoupper($serviceCode);
                $cost = is_array($data) ? (float)($data['cost'] ?? $data['price'] ?? 0.0) : (float)$data;
                $stock = is_array($data) ? (int)($data['count'] ?? $data['quantity'] ?? $data['stock'] ?? 10) : 10;

                // Validate code length and name
                if (strlen($serviceCode) < 1 || strlen($serviceCode) > 50) {
                    continue;
                }

                $services[] = [
                    'service_code' => $serviceCode,
                    'name' => trim((string)$name),
                    'cost' => $cost,
                    'stock' => max(0, $stock),
                    'is_active' => 1
                ];
            }
            return ['success' => true, 'services' => $services, 'message' => 'Services loaded'];
        }

        return ['success' => false, 'services' => [], 'message' => 'Unable to parse services data: ' . substr($body, 0, 80)];
    }

    /**
     * Request a new phone number
     */
    public function getNumber(string $serviceCode, string $countryCode, array $options = []): array
    {
        $params = [
            'action' => 'getNumber',
            'service' => $serviceCode,
            'country' => $countryCode
        ];

        if (!empty($options['server'])) {
            $params['server'] = $options['server'];
        }

        $response = $this->sendRequest($params);
        if (!$response['success']) {
            return [
                'success' => false,
                'activation_id' => '',
                'phone' => '',
                'cost' => 0.0,
                'message' => $response['error'] ?? 'Provider connection failure'
            ];
        }

        $body = trim($response['body']);

        // Typical response: ACCESS_NUMBER:12345678:919876543210
        if (str_starts_with($body, 'ACCESS_NUMBER:')) {
            $parts = explode(':', $body);
            $activationId = $parts[1] ?? '';
            $phone = $parts[2] ?? '';

            return [
                'success' => true,
                'activation_id' => $activationId,
                'phone' => $phone,
                'cost' => (float)($options['expected_cost'] ?? 0.0),
                'message' => 'Number allocated successfully'
            ];
        }

        // Common error strings
        $errorMap = [
            'NO_NUMBERS' => 'No phone numbers currently available for this service. Please retry in a few moments.',
            'NO_BALANCE' => 'Provider account balance exhausted. Please contact system administrator.',
            'BAD_KEY' => 'Invalid provider API credentials.',
            'BAD_SERVICE' => 'Service not supported in this region.',
            'BAD_ACTION' => 'Unsupported API command parameter.'
        ];

        $friendlyMessage = $errorMap[$body] ?? ("Provider response: " . $body);

        return [
            'success' => false,
            'activation_id' => '',
            'phone' => '',
            'cost' => 0.0,
            'message' => $friendlyMessage
        ];
    }

    /**
     * Retrieve status of an activation (polling for OTP)
     */
    public function getStatus(string $activationId): array
    {
        $response = $this->sendRequest([
            'action' => 'getStatus',
            'id' => $activationId
        ]);

        if (!$response['success']) {
            return [
                'success' => false,
                'status' => 'PENDING',
                'otp' => null,
                'full_sms' => null,
                'raw' => $response['error'] ?? ''
            ];
        }

        $body = trim($response['body']);

        // STATUS_WAIT_CODE
        if ($body === 'STATUS_WAIT_CODE') {
            return [
                'success' => true,
                'status' => 'WAITING',
                'otp' => null,
                'full_sms' => null,
                'raw' => $body
            ];
        }

        // STATUS_OK:123456
        if (str_starts_with($body, 'STATUS_OK:')) {
            $parts = explode(':', $body, 2);
            $otp = trim($parts[1] ?? '');
            return [
                'success' => true,
                'status' => 'RECEIVED',
                'otp' => $otp,
                'full_sms' => "Verification code: " . $otp,
                'raw' => $body
            ];
        }

        // STATUS_CANCEL
        if ($body === 'STATUS_CANCEL') {
            return [
                'success' => true,
                'status' => 'CANCELLED',
                'otp' => null,
                'full_sms' => null,
                'raw' => $body
            ];
        }

        return [
            'success' => true,
            'status' => 'UNKNOWN',
            'otp' => null,
            'full_sms' => null,
            'raw' => $body
        ];
    }

    /**
     * Update activation status (8 = cancel, 6 = complete, 1 = ready/next)
     */
    public function setStatus(string $activationId, int $statusCode): array
    {
        $response = $this->sendRequest([
            'action' => 'setStatus',
            'id' => $activationId,
            'status' => $statusCode
        ]);

        if (!$response['success']) {
            return ['success' => false, 'response' => '', 'message' => $response['error'] ?? 'Failed to update status'];
        }

        $body = trim($response['body']);
        // ACCESS_CANCEL, ACCESS_READY, ACCESS_ACTIVATION
        $success = str_starts_with($body, 'ACCESS_');

        return [
            'success' => $success,
            'response' => $body,
            'message' => $success ? 'Status updated' : 'Provider rejected status update: ' . $body
        ];
    }

    /**
     * Send HTTP cURL request with secret redaction and DB logging
     */
    protected function sendRequest(array $queryParams): array
    {
        $queryParams['api_key'] = $this->apiKey;
        $url = $this->apiUrl . '?' . http_build_query($queryParams);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'OTP-Platform-Engine/1.0',
            CURLOPT_HTTPHEADER => ['Accept: text/plain, application/json']
        ]);

        $startTime = microtime(true);
        $result = curl_exec($ch);
        $durationMs = (int)((microtime(true) - $startTime) * 1000);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Sanitize URL for logging (redact api_key)
        $loggedParams = $queryParams;
        $loggedParams['api_key'] = '***REDACTED***';
        $safeUrl = $this->apiUrl . '?' . http_build_query($loggedParams);

        // Log to database if PDO available
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO provider_logs (provider_id, action, request_url, request_payload, response_status, response_body, response_time_ms, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $this->providerId,
                    $queryParams['action'] ?? 'unknown',
                    $safeUrl,
                    null,
                    $httpCode,
                    $result !== false ? substr((string)$result, 0, 2000) : $error,
                    $durationMs
                ]);
            } catch (Exception $e) {
                // Ignore log write failure to prevent breaking main business flow
            }
        }

        if ($result === false) {
            return [
                'success' => false,
                'http_code' => $httpCode,
                'error' => "cURL network error: " . $error,
                'body' => null
            ];
        }

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'body' => (string)$result
        ];
    }
}

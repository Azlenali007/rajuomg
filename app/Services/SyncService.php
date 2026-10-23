<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Modules\Providers\OtpDoctorProvider;
use PDO;
use Exception;

/**
 * SyncService
 * Handles automated synchronization of countries, services, provider costs, and stock states.
 */
class SyncService
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Synchronize countries from provider
     */
    public function syncCountries(int $providerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return ['success' => false, 'message' => 'Provider not found'];
        }

        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);
        $result = $providerInstance->getCountries();

        if (!$result['success']) {
            return ['success' => false, 'message' => $result['message']];
        }

        $imported = 0;
        $updated = 0;

        $this->pdo->beginTransaction();
        try {
            $insStmt = $this->pdo->prepare("
                INSERT INTO countries (provider_id, country_code, country_name, dial_code, is_active, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE country_name = VALUES(country_name), dial_code = VALUES(dial_code)
            ");

            foreach ($result['countries'] as $c) {
                $code = strtolower(trim((string)$c['code']));
                $name = trim((string)$c['name']);
                $dial = $c['dial_code'] ?? null;

                $insStmt->execute([$providerId, $code, $name, $dial]);
                $imported++;
            }

            $this->pdo->prepare("UPDATE providers SET last_sync_at = NOW() WHERE id = ?")->execute([$providerId]);
            $this->pdo->commit();

            Logger::info("Country synchronization complete", ['provider_id' => $providerId, 'count' => $imported]);
            return ['success' => true, 'count' => $imported, 'message' => "Successfully synchronized {$imported} countries."];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Country sync failed: " . $e->getMessage(), ['provider_id' => $providerId]);
            return ['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()];
        }
    }

    /**
     * Synchronize services and stock for a country
     */
    public function syncServices(int $providerId, string $countryCode = 'in'): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return ['success' => false, 'message' => 'Provider not found'];
        }

        $cStmt = $this->pdo->prepare("SELECT id FROM countries WHERE provider_id = ? AND country_code = ? LIMIT 1");
        $cStmt->execute([$providerId, $countryCode]);
        $country = $cStmt->fetch();
        $countryId = $country ? (int)$country['id'] : 1;

        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);
        $result = $providerInstance->getServices($countryCode);

        if (!$result['success']) {
            return ['success' => false, 'message' => $result['message']];
        }

        $processed = 0;
        $this->pdo->beginTransaction();
        try {
            foreach ($result['services'] as $srv) {
                $serviceCode = strtolower(trim((string)$srv['service_code']));
                $serviceName = trim((string)$srv['name']);
                $cost = (float)($srv['cost'] ?? 0.0);
                $stockCount = (int)($srv['stock'] ?? 10);
                $stockStatus = $stockCount > 0 ? 'AVAILABLE' : 'UNAVAILABLE';

                // Check if base service exists, or insert new
                $findS = $this->pdo->prepare("SELECT id, custom_price, profit_margin_percent FROM services WHERE service_code = ? LIMIT 1");
                $findS->execute([$serviceCode]);
                $existing = $findS->fetch();

                if ($existing) {
                    $serviceId = (int)$existing['id'];
                } else {
                    $margin = 25.00;
                    $sellingPrice = round($cost * 1.25, 2);
                    $insS = $this->pdo->prepare("
                        INSERT INTO services (service_code, service_name, category, custom_price, profit_margin_percent, is_active, created_at)
                        VALUES (?, ?, 'general', ?, ?, 1, NOW())
                    ");
                    $insS->execute([$serviceCode, $serviceName, $sellingPrice, $margin]);
                    $serviceId = (int)$this->pdo->lastInsertId();
                }

                // Upsert provider_services mapping with stock and provider cost
                $upsertMap = $this->pdo->prepare("
                    INSERT INTO provider_services (provider_id, service_id, country_id, provider_service_code, provider_cost, stock_status, stock_count, last_checked_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        provider_cost = VALUES(provider_cost),
                        stock_status = VALUES(stock_status),
                        stock_count = VALUES(stock_count),
                        last_checked_at = NOW()
                ");
                $upsertMap->execute([$providerId, $serviceId, $countryId, $serviceCode, $cost, $stockStatus, $stockCount]);
                $processed++;
            }

            $this->pdo->prepare("UPDATE providers SET last_sync_at = NOW() WHERE id = ?")->execute([$providerId]);
            $this->pdo->commit();

            Logger::info("Service synchronization complete", ['provider_id' => $providerId, 'processed' => $processed]);
            return ['success' => true, 'count' => $processed, 'message' => "Synchronized {$processed} services for country {$countryCode}."];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Services sync failed: " . $e->getMessage(), ['provider_id' => $providerId]);
            return ['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()];
        }
    }
}

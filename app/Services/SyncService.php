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
     * Synchronize countries from provider API
     */
    public function syncCountries(int $providerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return ['success' => false, 'count' => 0, 'message' => 'Provider not found'];
        }

        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);
        $result = $providerInstance->getCountries();

        if (!$result['success']) {
            Logger::error("Country sync API error: {$result['message']}", ['provider_id' => $providerId]);
            return ['success' => false, 'count' => 0, 'message' => $result['message']];
        }

        if (empty($result['countries'])) {
            return ['success' => false, 'count' => 0, 'message' => 'Provider returned empty country dataset.'];
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        $this->pdo->beginTransaction();
        try {
            $selectStmt = $this->pdo->prepare("SELECT id FROM countries WHERE provider_id = ? AND country_code = ? LIMIT 1");
            $updateStmt = $this->pdo->prepare("UPDATE countries SET country_name = ?, dial_code = ?, is_active = 1 WHERE id = ?");
            $insertStmt = $this->pdo->prepare("
                INSERT INTO countries (provider_id, country_code, country_name, dial_code, is_active, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())
            ");

            foreach ($result['countries'] as $c) {
                // Ensure only active records from provider are imported
                if (isset($c['is_active']) && !$c['is_active']) {
                    $skipped++;
                    continue;
                }

                $code = strtolower(trim((string)($c['code'] ?? '')));
                $name = trim((string)($c['name'] ?? ''));
                $dial = !empty($c['dial_code']) ? trim((string)$c['dial_code']) : null;

                if (empty($code) || empty($name)) {
                    $skipped++;
                    continue;
                }

                $selectStmt->execute([$providerId, $code]);
                $existing = $selectStmt->fetch();

                if ($existing) {
                    $updateStmt->execute([$name, $dial, $existing['id']]);
                    $updated++;
                } else {
                    $insertStmt->execute([$providerId, $code, $name, $dial]);
                    $imported++;
                }
            }

            $this->pdo->prepare("UPDATE providers SET last_sync_at = NOW() WHERE id = ?")->execute([$providerId]);
            $this->pdo->commit();

            $total = $imported + $updated;
            Logger::info("Country synchronization complete", ['provider_id' => $providerId, 'imported' => $imported, 'updated' => $updated, 'skipped' => $skipped]);
            return [
                'success' => true,
                'count' => $total,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'message' => "Successfully synchronized {$total} active countries ({$imported} new, {$updated} updated, {$skipped} skipped/inactive)."
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Country sync database error: " . $e->getMessage(), ['provider_id' => $providerId]);
            return ['success' => false, 'count' => 0, 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'message' => 'Database error during country import: ' . $e->getMessage()];
        }
    }

    /**
     * Synchronize services and stock from provider API
     * Supports syncing a specific country or all active countries mapped to the provider.
     */
    public function syncServices(int $providerId, ?string $countryCode = null): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return ['success' => false, 'count' => 0, 'message' => 'Provider not found'];
        }

        // Determine countries to sync
        $countriesToSync = [];
        if (!empty($countryCode) && strtolower(trim($countryCode)) !== 'all') {
            $normalizedCode = strtolower(trim($countryCode));
            $cStmt = $this->pdo->prepare("SELECT * FROM countries WHERE provider_id = ? AND (LOWER(country_code) = ? OR id = ?) LIMIT 1");
            $cStmt->execute([$providerId, $normalizedCode, is_numeric($normalizedCode) ? (int)$normalizedCode : 0]);
            $country = $cStmt->fetch();

            if (!$country) {
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => "Country '{$countryCode}' does not exist in local records. Please sync countries from the provider API first."
                ];
            }
            $countriesToSync[] = $country;
        } else {
            // Retrieve all active countries for this provider
            $cStmt = $this->pdo->prepare("SELECT * FROM countries WHERE provider_id = ? AND is_active = 1 ORDER BY id ASC");
            $cStmt->execute([$providerId]);
            $countriesToSync = $cStmt->fetchAll();

            if (empty($countriesToSync)) {
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => 'No active countries found for this provider. Please synchronize countries from provider API first.'
                ];
            }
        }

        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);

        $totalMapped = 0;
        $totalImported = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $errors = [];

        $findServiceStmt = $this->pdo->prepare("SELECT id, custom_price, profit_margin_percent FROM services WHERE service_code = ? LIMIT 1");
        $insertServiceStmt = $this->pdo->prepare("
            INSERT INTO services (service_code, service_name, category, custom_price, profit_margin_percent, is_active, created_at)
            VALUES (?, ?, 'general', ?, 25.00, 1, NOW())
        ");
        $updateServiceStmt = $this->pdo->prepare("UPDATE services SET service_name = ?, is_active = 1, updated_at = NOW() WHERE id = ?");

        $findMapStmt = $this->pdo->prepare("SELECT id FROM provider_services WHERE provider_id = ? AND service_id = ? AND country_id = ? LIMIT 1");
        $insertMapStmt = $this->pdo->prepare("
            INSERT INTO provider_services (provider_id, service_id, country_id, provider_service_code, provider_cost, stock_status, stock_count, last_checked_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $updateMapStmt = $this->pdo->prepare("
            UPDATE provider_services SET provider_cost = ?, stock_status = ?, stock_count = ?, last_checked_at = NOW()
            WHERE id = ?
        ");

        foreach ($countriesToSync as $cItem) {
            $targetCountryId = (int)$cItem['id'];
            $targetCountryCode = (string)$cItem['country_code'];

            $result = $providerInstance->getServices($targetCountryCode);

            if (!$result['success']) {
                $errMsg = $result['message'] ?? 'Unknown error';
                $errors[] = "Country '{$cItem['country_name']}' ({$targetCountryCode}): {$errMsg}";
                continue;
            }

            if (empty($result['services'])) {
                continue;
            }

            $this->pdo->beginTransaction();
            try {
                foreach ($result['services'] as $srv) {
                    if (isset($srv['is_active']) && !$srv['is_active']) {
                        $totalSkipped++;
                        continue;
                    }

                    $serviceCode = strtolower(trim((string)($srv['service_code'] ?? '')));
                    $serviceName = trim((string)($srv['name'] ?? strtoupper($serviceCode)));
                    $cost = (float)($srv['cost'] ?? 0.0);
                    $stockCount = isset($srv['stock']) ? (int)$srv['stock'] : null;
                    $stockStatus = ($stockCount !== null && $stockCount <= 0) ? 'UNAVAILABLE' : 'AVAILABLE';

                    if (empty($serviceCode)) {
                        $totalSkipped++;
                        continue;
                    }

                    // Look up existing service to preserve custom selling prices and profit margins
                    $findServiceStmt->execute([$serviceCode]);
                    $existingService = $findServiceStmt->fetch();

                    if ($existingService) {
                        $serviceId = (int)$existingService['id'];
                        // Preserve custom pricing and profit margin
                        $updateServiceStmt->execute([$serviceName, $serviceId]);
                        $totalUpdated++;
                    } else {
                        $sellingPrice = round(max(0.50, $cost * 1.30), 2);
                        $insertServiceStmt->execute([$serviceCode, $serviceName, $sellingPrice]);
                        $serviceId = (int)$this->pdo->lastInsertId();
                        $totalImported++;
                    }

                    // Upsert provider_services real mapping
                    $findMapStmt->execute([$providerId, $serviceId, $targetCountryId]);
                    $existingMap = $findMapStmt->fetch();

                    if ($existingMap) {
                        $updateMapStmt->execute([$cost, $stockStatus, $stockCount, $existingMap['id']]);
                    } else {
                        $insertMapStmt->execute([$providerId, $serviceId, $targetCountryId, $serviceCode, $cost, $stockStatus, $stockCount]);
                    }
                    $totalMapped++;
                }

                $this->pdo->commit();
            } catch (Exception $e) {
                $this->pdo->rollBack();
                Logger::error("Database error syncing country {$targetCountryCode}: " . $e->getMessage(), ['provider_id' => $providerId]);
                $errors[] = "Database error for country {$targetCountryCode}: " . $e->getMessage();
            }
        }

        $this->pdo->prepare("UPDATE providers SET last_sync_at = NOW() WHERE id = ?")->execute([$providerId]);

        if ($totalMapped === 0 && !empty($errors)) {
            return [
                'success' => false,
                'count' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'message' => implode(' | ', array_slice($errors, 0, 3))
            ];
        }

        Logger::info("Service synchronization complete", [
            'provider_id' => $providerId,
            'mapped' => $totalMapped,
            'imported' => $totalImported,
            'updated' => $totalUpdated,
            'skipped' => $totalSkipped
        ]);

        return [
            'success' => true,
            'count' => $totalMapped,
            'imported' => $totalImported,
            'updated' => $totalUpdated,
            'skipped' => $totalSkipped,
            'message' => "Successfully synchronized {$totalMapped} provider service mappings ({$totalImported} new, {$totalUpdated} updated, {$totalSkipped} skipped)."
        ];
    }
}

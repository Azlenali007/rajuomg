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
     * Synchronize services and stock for a country from provider API
     */
    public function syncServices(int $providerId, string $countryCode = 'in'): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return ['success' => false, 'count' => 0, 'message' => 'Provider not found'];
        }

        // Find or create country mapping
        $countryCode = strtolower(trim($countryCode));
        $cStmt = $this->pdo->prepare("SELECT id FROM countries WHERE provider_id = ? AND country_code = ? LIMIT 1");
        $cStmt->execute([$providerId, $countryCode]);
        $country = $cStmt->fetch();

        if ($country) {
            $countryId = (int)$country['id'];
        } else {
            // Auto-create country entry if missing
            $insC = $this->pdo->prepare("INSERT INTO countries (provider_id, country_code, country_name, dial_code, is_active, created_at) VALUES (?, ?, ?, NULL, 1, NOW())");
            $insC->execute([$providerId, $countryCode, strtoupper($countryCode)]);
            $countryId = (int)$this->pdo->lastInsertId();
        }

        $providerInstance = new OtpDoctorProvider($provider['api_url'], $provider['api_key'], $this->pdo, $providerId);
        $result = $providerInstance->getServices($countryCode);

        if (!$result['success']) {
            Logger::error("Service sync API error: {$result['message']}", ['provider_id' => $providerId, 'country' => $countryCode]);
            return ['success' => false, 'count' => 0, 'message' => $result['message']];
        }

        if (empty($result['services'])) {
            return ['success' => false, 'count' => 0, 'message' => "Provider returned no services for country '{$countryCode}'."];
        }

        $importedServices = 0;
        $updatedServices = 0;
        $skippedServices = 0;
        $mapped = 0;

        $this->pdo->beginTransaction();
        try {
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

            foreach ($result['services'] as $srv) {
                // Ensure only active records from provider are imported
                if (isset($srv['is_active']) && !$srv['is_active']) {
                    $skippedServices++;
                    continue;
                }

                $serviceCode = strtolower(trim((string)($srv['service_code'] ?? '')));
                $serviceName = trim((string)($srv['name'] ?? strtoupper($serviceCode)));
                $cost = (float)($srv['cost'] ?? 0.0);
                $stockCount = (int)($srv['stock'] ?? 10);
                $stockStatus = $stockCount > 0 ? 'AVAILABLE' : 'UNAVAILABLE';

                if (empty($serviceCode) || empty($serviceName)) {
                    $skippedServices++;
                    continue;
                }

                // Check or insert into base services
                $findServiceStmt->execute([$serviceCode]);
                $existingService = $findServiceStmt->fetch();

                if ($existingService) {
                    $serviceId = (int)$existingService['id'];
                    // PRESERVE custom pricing and profit margin percent
                    $updateServiceStmt->execute([$serviceName, $serviceId]);
                    $updatedServices++;
                } else {
                    $sellingPrice = round(max(0.50, $cost * 1.30), 2);
                    $insertServiceStmt->execute([$serviceCode, $serviceName, $sellingPrice]);
                    $serviceId = (int)$this->pdo->lastInsertId();
                    $importedServices++;
                }

                // Upsert provider_services mapping
                $findMapStmt->execute([$providerId, $serviceId, $countryId]);
                $existingMap = $findMapStmt->fetch();

                if ($existingMap) {
                    $updateMapStmt->execute([$cost, $stockStatus, $stockCount, $existingMap['id']]);
                } else {
                    $insertMapStmt->execute([$providerId, $serviceId, $countryId, $serviceCode, $cost, $stockStatus, $stockCount]);
                }
                $mapped++;
            }

            $this->pdo->prepare("UPDATE providers SET last_sync_at = NOW() WHERE id = ?")->execute([$providerId]);
            $this->pdo->commit();

            Logger::info("Service synchronization complete", ['provider_id' => $providerId, 'mapped' => $mapped, 'imported' => $importedServices, 'updated' => $updatedServices, 'skipped' => $skippedServices]);
            return [
                'success' => true,
                'count' => $mapped,
                'imported' => $importedServices,
                'updated' => $updatedServices,
                'skipped' => $skippedServices,
                'message' => "Synchronized {$mapped} active services for country '{$countryCode}' ({$importedServices} new, {$updatedServices} updated, {$skippedServices} skipped)."
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            Logger::error("Services sync database error: " . $e->getMessage(), ['provider_id' => $providerId]);
            return ['success' => false, 'count' => 0, 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'message' => 'Database error during service import: ' . $e->getMessage()];
        }
    }

    /**
     * Import / Refresh Standard Verified Catalog
     * Safely seeds verified major services and countries without overwriting custom prices.
     */
    public function importStandardCatalog(int $providerId): array
    {
        $standardCountries = [
            ['code' => 'in', 'name' => 'India', 'dial_code' => '+91'],
            ['code' => 'us', 'name' => 'United States', 'dial_code' => '+1'],
            ['code' => 'gb', 'name' => 'United Kingdom', 'dial_code' => '+44'],
            ['code' => 'ca', 'name' => 'Canada', 'dial_code' => '+1'],
            ['code' => 'id', 'name' => 'Indonesia', 'dial_code' => '+62'],
            ['code' => 'fr', 'name' => 'France', 'dial_code' => '+33'],
            ['code' => 'de', 'name' => 'Germany', 'dial_code' => '+49'],
            ['code' => 'br', 'name' => 'Brazil', 'dial_code' => '+55'],
            ['code' => 'vn', 'name' => 'Vietnam', 'dial_code' => '+84'],
            ['code' => 'ph', 'name' => 'Philippines', 'dial_code' => '+63'],
            ['code' => 'ng', 'name' => 'Nigeria', 'dial_code' => '+234'],
            ['code' => 'ru', 'name' => 'Russia', 'dial_code' => '+7']
        ];

        $standardServices = [
            ['code' => 'wa', 'name' => 'WhatsApp', 'cost' => 0.40, 'price' => 0.85, 'category' => 'messaging'],
            ['code' => 'tg', 'name' => 'Telegram', 'cost' => 0.30, 'price' => 0.65, 'category' => 'messaging'],
            ['code' => 'go', 'name' => 'Google / Gmail / YouTube', 'cost' => 0.45, 'price' => 0.90, 'category' => 'ai'],
            ['code' => 'ig', 'name' => 'Instagram', 'cost' => 0.35, 'price' => 0.70, 'category' => 'social'],
            ['code' => 'ds', 'name' => 'Discord', 'cost' => 0.25, 'price' => 0.55, 'category' => 'messaging'],
            ['code' => 'oa', 'name' => 'OpenAI / ChatGPT', 'cost' => 0.60, 'price' => 1.20, 'category' => 'ai'],
            ['code' => 'tw', 'name' => 'Twitter / X', 'cost' => 0.35, 'price' => 0.75, 'category' => 'social'],
            ['code' => 'ms', 'name' => 'Microsoft / Outlook', 'cost' => 0.40, 'price' => 0.80, 'category' => 'ai'],
            ['code' => 'tt', 'name' => 'TikTok', 'cost' => 0.30, 'price' => 0.60, 'category' => 'social'],
            ['code' => 'am', 'name' => 'Amazon', 'cost' => 0.50, 'price' => 0.95, 'category' => 'social'],
            ['code' => 'nf', 'name' => 'Netflix', 'cost' => 0.45, 'price' => 0.90, 'category' => 'social'],
            ['code' => 'st', 'name' => 'Steam', 'cost' => 0.35, 'price' => 0.75, 'category' => 'social']
        ];

        $this->pdo->beginTransaction();
        try {
            // Upsert countries
            $cImported = 0;
            $cUpdated = 0;
            $chkC = $this->pdo->prepare("SELECT id FROM countries WHERE provider_id = ? AND country_code = ? LIMIT 1");
            $updC = $this->pdo->prepare("UPDATE countries SET country_name = ?, dial_code = ?, is_active = 1 WHERE id = ?");
            $insC = $this->pdo->prepare("INSERT INTO countries (provider_id, country_code, country_name, dial_code, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");

            foreach ($standardCountries as $sc) {
                $chkC->execute([$providerId, $sc['code']]);
                $existing = $chkC->fetch();
                if ($existing) {
                    $updC->execute([$sc['name'], $sc['dial_code'], $existing['id']]);
                    $cUpdated++;
                } else {
                    $insC->execute([$providerId, $sc['code'], $sc['name'], $sc['dial_code']]);
                    $cImported++;
                }
            }

            // Get primary country ID for mapping
            $chkC->execute([$providerId, 'in']);
            $primaryCountry = $chkC->fetch();
            $countryId = $primaryCountry ? (int)$primaryCountry['id'] : 1;

            // Upsert services (preserving existing custom price and profit margin)
            $sImported = 0;
            $sUpdated = 0;
            $chkS = $this->pdo->prepare("SELECT id, custom_price, profit_margin_percent FROM services WHERE service_code = ? LIMIT 1");
            $updS = $this->pdo->prepare("UPDATE services SET service_name = ?, is_active = 1, updated_at = NOW() WHERE id = ?");
            $insS = $this->pdo->prepare("INSERT INTO services (service_code, service_name, category, custom_price, profit_margin_percent, is_active, created_at) VALUES (?, ?, ?, ?, 25.00, 1, NOW())");
            
            $chkMap = $this->pdo->prepare("SELECT id FROM provider_services WHERE provider_id = ? AND service_id = ? AND country_id = ? LIMIT 1");
            $insMap = $this->pdo->prepare("INSERT INTO provider_services (provider_id, service_id, country_id, provider_service_code, provider_cost, stock_status, stock_count, last_checked_at) VALUES (?, ?, ?, ?, ?, 'AVAILABLE', 25, NOW())");
            $updMap = $this->pdo->prepare("UPDATE provider_services SET provider_cost = ?, stock_status = 'AVAILABLE', stock_count = 25, last_checked_at = NOW() WHERE id = ?");

            foreach ($standardServices as $ss) {
                $chkS->execute([$ss['code']]);
                $existing = $chkS->fetch();
                if ($existing) {
                    $serviceId = (int)$existing['id'];
                    // PRESERVE existing custom price and profit margin
                    $updS->execute([$ss['name'], $serviceId]);
                    $sUpdated++;
                } else {
                    $insS->execute([$ss['code'], $ss['name'], $ss['category'], $ss['price']]);
                    $serviceId = (int)$this->pdo->lastInsertId();
                    $sImported++;
                }

                $chkMap->execute([$providerId, $serviceId, $countryId]);
                $existingMap = $chkMap->fetch();
                if ($existingMap) {
                    $updMap->execute([$ss['cost'], $existingMap['id']]);
                } else {
                    $insMap->execute([$providerId, $serviceId, $countryId, $ss['code'], $ss['cost']]);
                }
            }

            $this->pdo->commit();
            $sTotal = $sImported + $sUpdated;
            $cTotal = $cImported + $cUpdated;
            return [
                'success' => true,
                'count' => $sTotal,
                'imported' => $sImported,
                'updated' => $sUpdated,
                'skipped' => 0,
                'countries_count' => $cTotal,
                'message' => "Successfully synchronized catalog: {$sTotal} services ({$sImported} new, {$sUpdated} updated) across {$cTotal} countries."
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'count' => 0, 'imported' => 0, 'updated' => 0, 'skipped' => 0, 'message' => 'Catalog import failed: ' . $e->getMessage()];
        }
    }
}

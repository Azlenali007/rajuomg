<?php
declare(strict_types=1);

/**
 * Cron: Scheduled Service and Country Catalog Synchronizer
 * Usage: 0 * * * * php /path/to/cron/sync_services.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied: CLI only.\n");
}

$lockFile = sys_get_temp_dir() . '/otp_cron_sync_services.lock';
$lockHandle = fopen($lockFile, 'c');
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    die("Another sync_services process is running.\n");
}

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Logger.php';
require_once dirname(__DIR__) . '/modules/Providers/ProviderInterface.php';
require_once dirname(__DIR__) . '/modules/Providers/OtpDoctorProvider.php';
require_once dirname(__DIR__) . '/app/Services/SyncService.php';

$pdo = \App\Core\Database::getConnection();
$syncService = new \App\Services\SyncService($pdo);

echo "[" . date('Y-m-d H:i:s') . "] Starting catalog synchronization...\n";

$providers = $pdo->query("SELECT id FROM providers WHERE is_active = 1")->fetchAll();
foreach ($providers as $p) {
    $c = $syncService->syncCountries((int)$p['id']);
    $s = $syncService->syncServices((int)$p['id'], 'in');
    echo "  ✔ Provider #{$p['id']}: {$c['message']} | {$s['message']}\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Catalog sync finished.\n";

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

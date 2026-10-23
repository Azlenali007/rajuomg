<?php
declare(strict_types=1);

/**
 * Cron: Check active orders with external provider and poll for incoming OTPs
 * Usage: * * * * * php /path/to/cron/check_orders.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied: CLI only.\n");
}

// File lock to prevent concurrent duplicate execution
$lockFile = sys_get_temp_dir() . '/otp_cron_check_orders.lock';
$lockHandle = fopen($lockFile, 'c');
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    die("Another check_orders process is already executing.\n");
}

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Logger.php';
require_once dirname(__DIR__) . '/modules/Providers/ProviderInterface.php';
require_once dirname(__DIR__) . '/modules/Providers/OtpDoctorProvider.php';
require_once dirname(__DIR__) . '/app/Services/WalletService.php';
require_once dirname(__DIR__) . '/app/Services/OrderService.php';

$pdo = \App\Core\Database::getConnection();
$orderService = new \App\Services\OrderService($pdo);

echo "[" . date('Y-m-d H:i:s') . "] Starting active order status check...\n";

// Query pending active orders
$stmt = $pdo->query("SELECT id FROM orders WHERE status = 'ACTIVE' AND expires_at > NOW() LIMIT 50");
$orders = $stmt->fetchAll();

$processed = 0;
foreach ($orders as $ord) {
    $result = $orderService->checkStatus((int)$ord['id']);
    if (!empty($result['otp'])) {
        echo "  ✔ Order #{$ord['id']} OTP received: {$result['otp']}\n";
    }
    $processed++;
}

echo "[" . date('Y-m-d H:i:s') . "] Completed check_orders. Checked {$processed} records.\n";

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

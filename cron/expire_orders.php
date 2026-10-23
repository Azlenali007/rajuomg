<?php
declare(strict_types=1);

/**
 * Cron: Expire timed-out orders and issue 100% automatic wallet refunds
 * Usage: */2 * * * * php /path/to/cron/expire_orders.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied: CLI only.\n");
}

$lockFile = sys_get_temp_dir() . '/otp_cron_expire_orders.lock';
$lockHandle = fopen($lockFile, 'c');
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    die("Another expire_orders process is already executing.\n");
}

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Logger.php';
require_once dirname(__DIR__) . '/app/Services/WalletService.php';
require_once dirname(__DIR__) . '/app/Services/OrderService.php';

$pdo = \App\Core\Database::getConnection();
$orderService = new \App\Services\OrderService($pdo);

echo "[" . date('Y-m-d H:i:s') . "] Checking for expired orders...\n";

$stmt = $pdo->query("SELECT id FROM orders WHERE status = 'ACTIVE' AND expires_at <= NOW()");
$expiredOrders = $stmt->fetchAll();

$count = 0;
foreach ($expiredOrders as $ord) {
    if ($orderService->expireOrder((int)$ord['id'])) {
        echo "  ✔ Expired & refunded order #{$ord['id']}\n";
        $count++;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Processed {$count} expired orders.\n";

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

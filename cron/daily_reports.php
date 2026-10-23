<?php
declare(strict_types=1);

/**
 * Cron: Daily Revenue & Volume Reporter
 * Usage: 0 0 * * * php /path/to/cron/daily_reports.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied: CLI only.\n");
}

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Core/Logger.php';

$pdo = \App\Core\Database::getConnection();

$date = date('Y-m-d', strtotime('-1 day'));
echo "[" . date('Y-m-d H:i:s') . "] Generating daily reconciliation report for {$date}...\n";

$ordersCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '{$date}'")->fetchColumn();
$deliveredCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '{$date}' AND status IN ('RECEIVED', 'COMPLETED')")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE DATE(created_at) = '{$date}' AND type = 'credit'")->fetchColumn();

echo "  Orders: {$ordersCount} | Delivered: {$deliveredCount} | Deposits: \${$revenue}\n";
\App\Core\Logger::info("Daily report generated", ['date' => $date, 'orders' => $ordersCount, 'revenue' => $revenue]);

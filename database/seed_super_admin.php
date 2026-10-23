<?php
declare(strict_types=1);

/**
 * OTP Platform - CLI Super Admin Seeder
 * Creates or updates the single authorized Super Admin account.
 * Usage: php database/seed_super_admin.php [email] [password] [name]
 */

if (php_sapi_name() !== 'cli') {
    die("Error: This script must be run from the command line interface.\n");
}

$dbConfig = require dirname(__DIR__) . '/config/database.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . "\n");
}

// Check existing super admins - exactly ONE allowed
$stmt = $pdo->prepare("SELECT id, email FROM users WHERE role = 'super_admin' LIMIT 2");
$stmt->execute();
$existingAdmins = $stmt->fetchAll();

$email = $argv[1] ?? 'admin@otpdoctor.in';
$password = $argv[2] ?? null;
$name = $argv[3] ?? 'Super Administrator';

if (!$password) {
    echo "Enter password for Super Admin ({$email}): ";
    $password = rtrim(fgets(STDIN));
    if (strlen($password) < 8) {
        die("Error: Password must be at least 8 characters long.\n");
    }
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

if (!empty($existingAdmins)) {
    // Update existing single super admin
    $adminId = $existingAdmins[0]['id'];
    $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ?, status = 'active', updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$name, $email, $hash, $adminId]);
    echo "✔ Super Admin updated successfully: ID {$adminId} ({$email})\n";
} else {
    // Insert fresh super admin
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, 'super_admin', 'active', NOW())");
    $insertStmt->execute([$name, $email, $hash]);
    $adminId = $pdo->lastInsertId();

    // Ensure wallet exists for admin
    $walletStmt = $pdo->prepare("INSERT INTO wallets (user_id, balance, currency, created_at) VALUES (?, 9999.0000, 'USD', NOW()) ON DUPLICATE KEY UPDATE updated_at = NOW()");
    $walletStmt->execute([$adminId]);

    echo "✔ Initial Super Admin created successfully: ID {$adminId} ({$email})\n";
}
echo "✔ Single Super Admin rule validated.\n";

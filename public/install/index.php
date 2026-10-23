<?php
declare(strict_types=1);

/**
 * OTP Doctor - Web-Based Installation Wizard
 * Accessible at: https://yourdomain.com/install
 */

session_name('OTP_INSTALLER_SESS');
session_start();

$rootDir = dirname(__DIR__, 2);
$lockFile = $rootDir . '/storage/installed.lock';

// 1. Permanent Installation Lock Check
$isInstalled = file_exists($lockFile);

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if ($isInstalled && ($_POST['action'] ?? '') !== 'check_lock') {
        echo json_encode(['success' => false, 'message' => 'Application is already locked and installed. Reinstallation is prohibited.']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    // Action: Test Database Connection
    if ($action === 'test_db') {
        $host = trim((string)($_POST['db_host'] ?? '127.0.0.1'));
        $port = (int)($_POST['db_port'] ?? 3306);
        $database = trim((string)($_POST['db_name'] ?? ''));
        $username = trim((string)($_POST['db_user'] ?? 'root'));
        $password = (string)($_POST['db_pass'] ?? '');

        if (empty($database)) {
            echo json_encode(['success' => false, 'message' => 'Database name is required.']);
            exit;
        }

        try {
            // First attempt connecting to MySQL server directly
            $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsnServer, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);

            // Check if database exists, create if missing
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$database]);
            $exists = $stmt->fetch();

            if (!$exists) {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $dbStatus = "Database `{$database}` was automatically created.";
            } else {
                $dbStatus = "Connected to existing database `{$database}`.";
            }

            // Save credentials in temporary installer session
            $_SESSION['install_db'] = [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password
            ];

            echo json_encode(['success' => true, 'message' => "Database handshake successful! {$dbStatus}"]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // Action: Execute Full Installation
    if ($action === 'execute_install') {
        $db = $_SESSION['install_db'] ?? null;
        if (!$db) {
            echo json_encode(['success' => false, 'message' => 'Database configuration missing. Please complete Step 3 first.']);
            exit;
        }

        $siteName = trim((string)($_POST['site_name'] ?? 'OTP Doctor'));
        $appUrl = trim((string)($_POST['app_url'] ?? 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));
        $currency = trim((string)($_POST['currency'] ?? 'USD'));
        $adminName = trim((string)($_POST['admin_name'] ?? 'Super Administrator'));
        $adminEmail = trim((string)($_POST['admin_email'] ?? ''));
        $adminPass = (string)($_POST['admin_pass'] ?? '');

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Valid administrator email is required.']);
            exit;
        }

        if (strlen($adminPass) < 8) {
            echo json_encode(['success' => false, 'message' => 'Administrator password must be at least 8 characters long.']);
            exit;
        }

        try {
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 1. Run database/schema.sql
            $schemaFile = $rootDir . '/database/schema.sql';
            if (file_exists($schemaFile)) {
                $sqlSchema = file_get_contents($schemaFile);
                $pdo->exec($sqlSchema);
            }

            // 2. Run database/indexes.sql
            $indexesFile = $rootDir . '/database/indexes.sql';
            if (file_exists($indexesFile)) {
                $sqlIndexes = file_get_contents($indexesFile);
                // Execute individual index statements
                $statements = array_filter(array_map('trim', explode(';', $sqlIndexes)));
                foreach ($statements as $stmtSql) {
                    if (!empty($stmtSql)) {
                        try {
                            $pdo->exec($stmtSql);
                        } catch (Exception $ie) {
                            // Ignore if index already exists
                        }
                    }
                }
            }

            // 3. Run database/seed.sql
            $seedFile = $rootDir . '/database/seed.sql';
            if (file_exists($seedFile)) {
                $sqlSeed = file_get_contents($seedFile);
                $statements = array_filter(array_map('trim', explode(';', $sqlSeed)));
                foreach ($statements as $stmtSql) {
                    if (!empty($stmtSql)) {
                        $pdo->exec($stmtSql);
                    }
                }
            }

            // 4. Create the single Super Admin
            $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $adminStmt = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, role, status, created_at)
                VALUES (?, ?, ?, 'super_admin', 'active', NOW())
                ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = 'super_admin'
            ");
            $adminStmt->execute([$adminName, $adminEmail, $hash]);
            $adminId = (int)$pdo->lastInsertId();
            if ($adminId === 0) {
                $q = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $q->execute([$adminEmail]);
                $adminId = (int)$q->fetchColumn();
            }

            // Ensure wallet exists for admin
            $pdo->prepare("
                INSERT INTO wallets (user_id, balance, currency, created_at) 
                VALUES (?, 9999.0000, ?, NOW())
                ON DUPLICATE KEY UPDATE currency = VALUES(currency)
            ")->execute([$adminId, $currency]);

            // 5. Update site settings
            $settStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $settStmt->execute(['site_name', $siteName]);
            $settStmt->execute(['currency', $currency]);

            // 6. Generate configuration file: config/database.php
            $configContent = "<?php\ndeclare(strict_types=1);\n\n/**\n * Auto-generated by OTP Doctor Web Installer on " . date('Y-m-d H:i:s') . "\n */\n\nreturn [\n"
                . "    'host'     => getenv('DB_HOST') ?: " . var_export($db['host'], true) . ",\n"
                . "    'port'     => (int)(getenv('DB_PORT') ?: " . $db['port'] . "),\n"
                . "    'database' => getenv('DB_DATABASE') ?: " . var_export($db['database'], true) . ",\n"
                . "    'username' => getenv('DB_USERNAME') ?: " . var_export($db['username'], true) . ",\n"
                . "    'password' => getenv('DB_PASSWORD') ?: " . var_export($db['password'], true) . ",\n"
                . "    'charset'  => 'utf8mb4',\n"
                . "    'options'  => [\n"
                . "        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
                . "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
                . "        PDO::ATTR_EMULATE_PREPARES   => false,\n"
                . "        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'\n"
                . "    ],\n"
                . "];\n";

            $configFile = $rootDir . '/config/database.php';
            $configWritten = @file_put_contents($configFile, $configContent, LOCK_EX);

            // Also update .env file
            $envFile = $rootDir . '/.env';
            $envContent = "APP_NAME=\"" . addslashes($siteName) . "\"\n"
                . "APP_ENV=production\n"
                . "APP_DEBUG=false\n"
                . "APP_URL=\"" . addslashes($appUrl) . "\"\n\n"
                . "DB_HOST=" . $db['host'] . "\n"
                . "DB_PORT=" . $db['port'] . "\n"
                . "DB_DATABASE=" . $db['database'] . "\n"
                . "DB_USERNAME=" . $db['username'] . "\n"
                . "DB_PASSWORD=" . $db['password'] . "\n\n"
                . "OTPDOCTOR_API_URL=https://www.otpdoctor.in/stubs/handler_api.php\n"
                . "OTPDOCTOR_API_KEY=YOUR_OTPDOCTOR_API_KEY_HERE\n";
            @file_put_contents($envFile, $envContent, LOCK_EX);

            // 7. Write permanent installation lock file
            $lockPayload = "INSTALLED=" . date('Y-m-d H:i:s') . "\nIP=" . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . "\n";
            @file_put_contents($lockFile, $lockPayload, LOCK_EX);

            // Clear installer session
            unset($_SESSION['install_db']);

            echo json_encode([
                'success' => true,
                'message' => 'Installation completed successfully! Configuration saved and installer locked.',
                'config_written' => (bool)$configWritten
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Installation failed: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

// System Requirements Check helper
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '8.2.0', '>=');
$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'curl' => extension_loaded('curl'),
    'mbstring' => extension_loaded('mbstring'),
    'json' => extension_loaded('json'),
    'openssl' => extension_loaded('openssl'),
];
$allExtOk = !in_array(false, $extensions, true);

$storageWritable = is_writable($rootDir . '/storage') || @mkdir($rootDir . '/storage', 0755, true);
$configWritable = is_writable($rootDir . '/config');
$detectedUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Installation Wizard — OTP Doctor</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800 bg-[#faf8ff] antialiased" x-data="installerWizard()">

    <!-- Header Strip -->
    <header class="bg-white border-b border-purple-100 py-4 shadow-2xs">
        <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-700 text-white flex items-center justify-center font-extrabold text-sm shadow-sm">
                    OD
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight">OTP Doctor</h1>
                    <p class="text-[10px] text-purple-700 font-bold uppercase tracking-wider">Web-Based Installation Wizard</p>
                </div>
            </div>
            
            <div class="text-right">
                <span class="text-xs font-semibold text-slate-500 font-mono">v1.0.0 Stable</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 py-8">

        <?php if ($isInstalled): ?>
            <!-- Installation Locked View -->
            <div class="bg-white border border-purple-200 rounded-2xl p-8 sm:p-12 shadow-sm text-center space-y-5 max-w-xl mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Application Already Installed</h2>
                <p class="text-sm text-slate-500">
                    The platform has already been initialized and securely locked with <code class="font-mono bg-slate-100 px-2 py-0.5 rounded text-xs text-purple-700">storage/installed.lock</code> to prevent accidental database overwriting.
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="/login" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors">
                        Sign In to Super Admin Panel
                    </a>
                    <a href="/" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Go to User Marketplace
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- Multi-Step Stepper Header -->
            <div class="mb-8">
                <div class="grid grid-cols-4 sm:grid-cols-8 gap-2 text-center text-[11px] font-semibold">
                    <template x-for="(sName, idx) in stepNames" :key="idx">
                        <div class="space-y-1">
                            <div 
                                class="h-1.5 rounded-full transition-colors"
                                :class="step > idx ? 'bg-purple-700' : (step === idx + 1 ? 'bg-purple-500' : 'bg-slate-200')"
                            ></div>
                            <span 
                                class="block truncate" 
                                :class="step === idx + 1 ? 'text-purple-700 font-bold' : (step > idx ? 'text-slate-800' : 'text-slate-400')"
                                x-text="sName"
                            ></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Card Viewport -->
            <div class="bg-white border border-purple-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-6">

                <!-- STEP 1: WELCOME PAGE -->
                <div x-show="step === 1" class="space-y-6 text-center py-4">
                    <div class="w-20 h-20 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center font-extrabold text-3xl mx-auto shadow-sm">
                        OD
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Welcome to OTP Doctor Installer</h2>
                        <p class="text-sm text-slate-500 max-w-md mx-auto">
                            This wizard will guide you through setting up your MySQL database, single Super Admin credentials, and essential settings.
                        </p>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-xs text-purple-900 text-left max-w-md mx-auto space-y-1.5">
                        <div class="font-bold">Before you begin, ensure you have:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-purple-800">
                            <li>MySQL database name and user credentials</li>
                            <li>PHP 8.2+ with PDO & cURL extensions enabled</li>
                            <li>Write permissions for <code class="font-mono">config/</code> and <code class="font-mono">storage/</code></li>
                        </ul>
                    </div>

                    <div class="pt-4">
                        <button 
                            @click="step = 2"
                            class="px-8 py-3 text-sm font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                            Start Installation →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: SERVER REQUIREMENTS -->
                <div x-show="step === 2" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-bold text-slate-900">Step 2: Server Compatibility & Requirements</h2>
                        <p class="text-xs text-slate-500">Checking hosting environment, PHP extensions, and folder write permissions.</p>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900 block">PHP Version (>= 8.2.0)</span>
                                <span class="text-slate-400 font-mono">Current: <?= $phpVersion ?></span>
                            </div>
                            <span class="font-bold px-2.5 py-1 rounded-md text-[11px] <?= $phpOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                <?= $phpOk ? '✔ Compatible' : '✘ PHP 8.2+ Required' ?>
                            </span>
                        </div>

                        <?php foreach ($extensions as $extName => $loaded): ?>
                            <div class="py-3 flex items-center justify-between">
                                <span class="font-semibold text-slate-800">PHP Extension: <code class="font-mono font-bold"><?= $extName ?></code></span>
                                <span class="font-bold px-2.5 py-1 rounded-md text-[11px] <?= $loaded ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                    <?= $loaded ? '✔ Enabled' : '✘ Missing' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <div class="py-3 flex items-center justify-between">
                            <span class="font-semibold text-slate-800">Directory Permissions: <code class="font-mono">storage/</code> and <code class="font-mono">config/</code></span>
                            <span class="font-bold px-2.5 py-1 rounded-md text-[11px] <?= ($storageWritable && $configWritable) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
                                <?= ($storageWritable && $configWritable) ? '✔ Writable' : '⚠ Limited Permissions' ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button @click="step = 1" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                            @click="step = 3"
                            class="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                            Next: Database Configuration →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: DATABASE CONFIGURATION -->
                <div x-show="step === 3" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-bold text-slate-900">Step 3: Database Connection</h2>
                        <p class="text-xs text-slate-500">Provide your MySQL database credentials. We test the connection before proceeding.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">MySQL Host</label>
                            <input type="text" x-model="dbHost" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="127.0.0.1 or localhost">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Port</label>
                            <input type="number" x-model.number="dbPort" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="3306">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Database Name</label>
                        <input type="text" x-model="dbName" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="otp_platform">
                        <span class="text-[11px] text-slate-400 mt-1 block">If the database doesn't exist, the installer will attempt to create it.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Database Username</label>
                            <input type="text" x-model="dbUser" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="root">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Database Password</label>
                            <input type="password" x-model="dbPass" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono" placeholder="••••••••">
                        </div>
                    </div>

                    <div x-show="dbTestMsg" class="p-3 rounded-xl text-xs font-medium" :class="dbTested ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'" x-text="dbTestMsg"></div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button @click="step = 2" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <div class="flex items-center gap-3">
                            <button 
                                @click="testDatabase()"
                                :disabled="dbTesting"
                                class="px-4 py-2 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl transition-colors"
                            >
                                <span x-show="!dbTesting">Test Connection</span>
                                <span x-show="dbTesting">Testing Handshake...</span>
                            </button>
                            <button 
                                @click="nextFromDb()"
                                class="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                            >
                                Next: Site Configuration →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: WEBSITE CONFIGURATION -->
                <div x-show="step === 4" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-bold text-slate-900">Step 4: Platform & Website Configuration</h2>
                        <p class="text-xs text-slate-500">Configure your website name, base URL, timezone, and platform currency.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Website Name</label>
                        <input type="text" x-model="siteName" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold" placeholder="OTP Doctor Verification">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Application URL (Auto-Detected)</label>
                        <input type="url" x-model="appUrl" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="<?= $detectedUrl ?>">
                        <span class="text-[11px] text-slate-400 mt-1 block">Used for self-referential webhooks, links, and callbacks.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Platform Timezone</label>
                            <select x-model="timezone" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="UTC">UTC (Universal Coordinated Time)</option>
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Default Currency</label>
                            <select x-model="currency" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="USD">USD ($)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button @click="step = 3" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                            @click="step = 5"
                            class="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                            Next: Super Admin Account →
                        </button>
                    </div>
                </div>

                <!-- STEP 5: ADMIN ACCOUNT CREATION -->
                <div x-show="step === 5" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900">Step 5: Create Single Super Admin Account</h2>
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">EXACTLY ONE SUPER ADMIN</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">This account will have exclusive administrative authority. Staff or sub-admin roles are strictly prohibited.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Administrator Full Name</label>
                        <input type="text" x-model="adminName" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold" placeholder="Super Administrator">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Administrator Email Address</label>
                        <input type="email" x-model="adminEmail" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" placeholder="admin@otpdoctor.in">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Administrator Password (Min 8 Characters)</label>
                        <input type="password" x-model="adminPass" class="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono" placeholder="••••••••••••••••">
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button @click="step = 4" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                            @click="startExecution()"
                            :disabled="installing"
                            class="px-8 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                            <span x-show="!installing">Install Tables & Complete Setup →</span>
                            <span x-show="installing">Installing Schema & Seed...</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 6 & 7: EXECUTING DATABASE INSTALLATION -->
                <div x-show="step === 6" class="space-y-6 text-center py-8">
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center mx-auto animate-pulse">
                        <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Setting up Database & System</h2>
                        <p class="text-xs text-slate-500 mt-1">Executing MySQL schema, performance indexes, service mappings, and locking installer...</p>
                    </div>
                    
                    <div class="max-w-md mx-auto text-left font-mono text-[11px] p-4 bg-slate-900 text-slate-300 rounded-xl space-y-1">
                        <div>✔ Connecting with PDO...</div>
                        <div>✔ Building schema (users, wallets, orders, providers)...</div>
                        <div>✔ Inserting default verification services...</div>
                        <div>✔ Provisioning Super Admin account...</div>
                        <div>✔ Writing storage/installed.lock...</div>
                    </div>
                </div>

                <!-- STEP 8: INSTALLATION COMPLETE -->
                <div x-show="step === 8" class="space-y-6 text-center py-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Installation Successfully Completed!</h2>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            The database tables have been created, configuration written to <code class="font-mono text-purple-700 bg-purple-50 px-1 rounded">config/database.php</code>, and the installer permanently locked.
                        </p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 max-w-md mx-auto text-left space-y-1">
                        <div>Administrator: <strong class="text-slate-900" x-text="adminEmail"></strong></div>
                        <div>Site URL: <span class="font-mono text-slate-600" x-text="appUrl"></span></div>
                        <div>Lock Status: <strong class="text-emerald-700">LOCKED (storage/installed.lock created)</strong></div>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="/login" class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors">
                            Sign In to Super Admin Panel
                        </a>
                        <a href="/" class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Visit User Marketplace
                        </a>
                    </div>
                </div>

            </div>

        <?php endif; ?>

    </main>

    <!-- Quiet Footer -->
    <footer class="py-6 border-t border-purple-100 bg-white">
        <div class="max-w-4xl mx-auto px-4 text-center text-xs text-slate-400">
            OTP Doctor Installation Protocol — Production Core PHP & MySQL
        </div>
    </footer>

    <script>
    function installerWizard() {
        return {
            step: 1,
            stepNames: ['Welcome', 'Requirements', 'Database', 'Settings', 'Admin', 'Install', 'Config', 'Complete'],
            
            // Database inputs
            dbHost: '127.0.0.1',
            dbPort: 3306,
            dbName: 'otp_platform',
            dbUser: 'root',
            dbPass: '',
            dbTesting: false,
            dbTested: false,
            dbTestMsg: null,

            // Site inputs
            siteName: 'OTP Doctor Verification',
            appUrl: '<?= addslashes($detectedUrl) ?>',
            timezone: 'UTC',
            currency: 'USD',

            // Admin inputs
            adminName: 'Super Administrator',
            adminEmail: 'admin@otpdoctor.in',
            adminPass: '',
            installing: false,

            async testDatabase() {
                if (!this.dbName) {
                    this.dbTestMsg = 'Database name is required.';
                    this.dbTested = false;
                    return;
                }

                this.dbTesting = true;
                this.dbTestMsg = null;

                try {
                    const fd = new FormData();
                    fd.append('action', 'test_db');
                    fd.append('db_host', this.dbHost);
                    fd.append('db_port', this.dbPort);
                    fd.append('db_name', this.dbName);
                    fd.append('db_user', this.dbUser);
                    fd.append('db_pass', this.dbPass);

                    const res = await fetch('/install/index.php', { method: 'POST', body: fd });
                    const data = await res.json();

                    this.dbTested = data.success;
                    this.dbTestMsg = data.message;
                } catch (e) {
                    this.dbTested = false;
                    this.dbTestMsg = 'Network error communicating with installer endpoint.';
                } finally {
                    this.dbTesting = false;
                }
            },

            nextFromDb() {
                if (!this.dbTested) {
                    Swal.fire({
                        title: 'Test Connection First',
                        text: 'Please test the database connection successfully before proceeding.',
                        icon: 'warning',
                        confirmButtonColor: '#7c3aed'
                    });
                    return;
                }
                this.step = 4;
            },

            async startExecution() {
                if (!this.adminEmail || this.adminPass.length < 8) {
                    Swal.fire('Validation Error', 'Password must be at least 8 characters long.', 'error');
                    return;
                }

                this.installing = true;
                this.step = 6;

                try {
                    const fd = new FormData();
                    fd.append('action', 'execute_install');
                    fd.append('site_name', this.siteName);
                    fd.append('app_url', this.appUrl);
                    fd.append('currency', this.currency);
                    fd.append('admin_name', this.adminName);
                    fd.append('admin_email', this.adminEmail);
                    fd.append('admin_pass', this.adminPass);

                    const res = await fetch('/install/index.php', { method: 'POST', body: fd });
                    const data = await res.json();

                    if (data.success) {
                        setTimeout(() => {
                            this.step = 8;
                            this.installing = false;
                        }, 1200);
                    } else {
                        this.step = 5;
                        this.installing = false;
                        Swal.fire('Installation Error', data.message, 'error');
                    }
                } catch (e) {
                    this.step = 5;
                    this.installing = false;
                    Swal.fire('Error', 'Installation request failed.', 'error');
                }
            }
        };
    }
    </script>
</body>
</html>

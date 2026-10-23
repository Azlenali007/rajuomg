<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton Database Connection Manager
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                Logger::error("Database Connection Failure: " . $e->getMessage());
                if (getenv('APP_DEBUG') === 'true') {
                    throw new RuntimeException("Database error: " . $e->getMessage());
                }
                http_response_code(500);
                require dirname(__DIR__, 2) . '/resources/views/errors/500.php';
                exit;
            }
        }
        return self::$instance;
    }
}

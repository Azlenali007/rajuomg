<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PSR-compliant simple file logger with secrets sanitization
 */
class Logger
{
    private static ?string $logFile = null;

    private static function init(): void
    {
        if (self::$logFile === null) {
            $path = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            self::$logFile = $path . '/app.log';
        }
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();
        $date = date('Y-m-d H:i:s');
        
        // Redact any sensitive tokens, passwords, or keys in context
        $safeContext = self::redact($context);
        $contextStr = !empty($safeContext) ? ' ' . json_encode($safeContext) : '';
        
        $line = sprintf("[%s] [%s] %s%s\n", $date, strtoupper($level), $message, $contextStr);
        @file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    private static function redact(array $data): array
    {
        $sensitiveKeys = ['password', 'api_key', 'key_secret', 'secret', 'token', 'auth', 'card'];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $data[$key] = self::redact($val);
            } elseif (is_string($key)) {
                foreach ($sensitiveKeys as $sensitive) {
                    if (stripos($key, $sensitive) !== false) {
                        $data[$key] = '***REDACTED***';
                        break;
                    }
                }
            }
        }
        return $data;
    }
}

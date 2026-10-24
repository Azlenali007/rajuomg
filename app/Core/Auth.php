<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Authentication and Role Authorization Service
 */
class Auth
{
    public static function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id'    => (int)Session::get('user_id'),
            'name'  => (string)Session::get('user_name'),
            'email' => (string)Session::get('user_email'),
            'role'  => (string)Session::get('user_role'),
        ];
    }

    public static function id(): ?int
    {
        return Session::get('user_id') ? (int)Session::get('user_id') : null;
    }

    public static function isSuperAdmin(): bool
    {
        return self::check() && Session::get('user_role') === 'super_admin';
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', (string)$user['name']);
        Session::set('user_email', (string)$user['email']);
        Session::set('user_role', (string)$user['role']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required.', 'redirect' => '/login']);
                exit;
            }
            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
    }

    public static function requireSuperAdmin(): void
    {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            require dirname(__DIR__, 2) . '/resources/views/errors/403.php';
            exit;
        }
    }
}

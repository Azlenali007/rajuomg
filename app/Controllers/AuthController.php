<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use PDO;

class AuthController
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        View::render('auth/login', ['error' => Session::flash('error'), 'success' => Session::flash('success')], null);
    }

    public function processLogin(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in both email and password.');
            header('Location: /login');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Invalid email credentials or password.');
            header('Location: /login');
            exit;
        }

        if ($user['status'] === 'suspended') {
            Session::flash('error', 'Your account has been suspended. Please contact support.');
            header('Location: /login');
            exit;
        }

        // Update last login
        $this->pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);

        Auth::login($user);

        if ($user['role'] === 'super_admin') {
            header('Location: /admin/dashboard');
        } else {
            header('Location: /dashboard');
        }
        exit;
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        View::render('auth/register', ['error' => Session::flash('error')], null);
    }

    public function processRegister(): void
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            Session::flash('error', 'Please provide a valid name, email, and password (minimum 6 characters).');
            header('Location: /register');
            exit;
        }

        // Check if email already registered
        $chk = $this->pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            Session::flash('error', 'This email is already registered. Please log in.');
            header('Location: /register');
            exit;
        }

        // Enforce user role (no public admin registration permitted)
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
        $ins->execute([$name, $email, $hash]);
        $userId = (int)$this->pdo->lastInsertId();

        // Create empty wallet
        $wIns = $this->pdo->prepare("INSERT INTO wallets (user_id, balance, currency, created_at) VALUES (?, 0.0000, 'USD', NOW())");
        $wIns->execute([$userId]);

        Session::flash('success', 'Registration successful! You may now sign in.');
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}

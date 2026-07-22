<?php

namespace App\Core;

class Auth
{
    public static function login(string $email, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user_id']    = (int) $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];

        if (defined('SESSION_SECRET')) {
            $_SESSION['auth_secret'] = SESSION_SECRET;
        }

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function currentUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role'  => $_SESSION['user_role'],
        ];
    }

    public static function requireAuth(array $allowedRoles = []): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!empty($allowedRoles)) {
            $user = self::currentUser();
            if (!in_array($user['role'], $allowedRoles, true)) {
                http_response_code(403);
                header('Location: /?error=unauthorized');
                exit;
            }
        }
    }

    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function hasRole(string $role): bool
    {
        $user = self::currentUser();
        return $user && $user['role'] === $role;
    }

    public static function hasAnyRole(array $roles): bool
    {
        $user = self::currentUser();
        if (!$user) {
            return false;
        }
        return in_array($user['role'], $roles, true);
    }

    public static function getUserPermissions(): array
    {
        $user = self::currentUser();
        if (!$user) {
            return [];
        }

        return match ($user['role']) {
            'admin' => [
                'notices.create',
                'notices.edit',
                'notices.delete',
                'notices.approve',
                'notices.reject',
                'notices.pin',
                'notices.duplicate',
                'notices.view-all',
                'notices.bookmark',
                'users.manage',
                'categories.manage',
                'faculties.manage',
                'departments.manage',
                'programmes.manage',
                'levels.manage',
                'reports.view',
                'reports.generate',
                'analytics.view',
                'activity-log.view',
            ],
            'staff' => [
                'notices.create',
                'notices.edit',
                'notices.delete',
                'notices.view-all',
                'notices.bookmark',
            ],
            'student' => [
                'notices.view',
                'notices.bookmark',
            ],
            default => [],
        };
    }
}

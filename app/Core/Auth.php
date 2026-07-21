<?php
/**
 * Auth — Session/Authentication Helper
 *
 * Manages user login/logout, session state, role-based access control,
 * and CSRF token generation/validation.
 *
 * All POST/DELETE state-changing endpoints must validate the CSRF token
 * before processing.
 */

namespace App\Core;

class Auth
{
    /**
     * Attempt to authenticate a user by email and password.
     * Uses password_verify() to check the hashed password.
     *
     * @param string $email
     * @param string $password
     * @return bool True on successful login
     */
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

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']    = (int) $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];

        // Update the session secret from config
        if (defined('SESSION_SECRET')) {
            $_SESSION['auth_secret'] = SESSION_SECRET;
        }

        return true;
    }

    /**
     * Destroy the current session and log the user out.
     */
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

    /**
     * Check if a user is currently logged in.
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the currently logged-in user's data from session.
     *
     * @return array|null Associative array with keys: id, name, email, role
     */
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

    /**
     * Require authentication. If not logged in, redirect to login page.
     * If $allowedRoles is provided, also check that the user has one of
     * the specified roles.
     *
     * @param array $allowedRoles e.g., ['super_admin', 'admin']
     */
    public static function requireAuth(array $allowedRoles = []): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!empty($allowedRoles)) {
            $user = self::currentUser();
            if (!in_array($user['role'], $allowedRoles, true)) {
                // Unauthorized — redirect with error
                http_response_code(403);
                header('Location: /?error=unauthorized');
                exit;
            }
        }
    }

    /**
     * Generate a CSRF token and store it in the session.
     *
     * @return string The generated token
     */
    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Retrieve the current CSRF token from session (generates one if missing).
     *
     * @return string
     */
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token against the one stored in the session.
     * Compares using hash_equals() to prevent timing attacks.
     *
     * @param string $token The token submitted with the form
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if the current user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        $user = self::currentUser();
        return $user && $user['role'] === $role;
    }
}

<?php
/**
 * AuthController — Handles authentication (login, logout, registration).
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\User;

class AuthController
{
    /**
     * Display the login form (GET /login).
     */
    public function loginForm(array $params = []): void
    {
        if (Auth::isLoggedIn()) {
            header('Location: /admin/dashboard');
            exit;
        }
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/login.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    /**
     * Process login submission (POST /login).
     */
    public function login(array $params = []): void
    {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            header('Location: /login');
            exit;
        }

        if (Auth::login($email, $password)) {
            $user = Auth::currentUser();
            // Redirect based on role
            if ($user['role'] === 'viewer') {
                header('Location: /');
            } else {
                header('Location: /admin/dashboard');
            }
            exit;
        }

        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: /login');
        exit;
    }

    /**
     * Logout and destroy session (POST /logout).
     */
    public function logout(array $params = []): void
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    /**
     * Display registration form (GET /register).
     */
    public function registerForm(array $params = []): void
    {
        require __DIR__ . '/../Views/layouts/header.php';
        // Registration form would go here
        echo '<div class="container"><h1>Register</h1><p>Self-registration is available for viewer accounts only.</p></div>';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    /**
     * Process registration (POST /register).
     */
    public function register(array $params = []): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /register');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /register');
            exit;
        }

        $userModel = new User();
        $existing = $userModel->findByEmail($email);
        if ($existing) {
            $_SESSION['error'] = 'Email already registered.';
            header('Location: /register');
            exit;
        }

        $userModel->create($name, $email, $password, 'viewer');
        Auth::login($email, $password);
        header('Location: /');
        exit;
    }
}

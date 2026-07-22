<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

class AuthController
{
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

    public function login(array $params = []): void
    {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            header('Location: /login');
            exit;
        }

        if (Auth::login($email, $password)) {
            $user = Auth::currentUser();
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            } elseif ($user['role'] === 'staff') {
                header('Location: /staff/dashboard');
            } else {
                header('Location: /');
            }
            exit;
        }

        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: /login');
        exit;
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    public function registerForm(array $params = []): void
    {
        require __DIR__ . '/../Views/layouts/header.php';
        echo '<div class="container"><h1>Register</h1><p>Self-registration is available for student accounts only.</p></div>';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

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

        $userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'role'     => 'student',
        ]);
        Auth::login($email, $password);
        header('Location: /');
        exit;
    }

    public function forgotPassword(array $params = []): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid security token.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        if (!$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Email is required.']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            echo json_encode(['message' => 'If that email exists, a reset link has been sent.']);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $db = Database::getInstance();
        $db->execute(
            'UPDATE users SET reset_token = :token, reset_token_expires = NOW() + INTERVAL \'1 hour\' WHERE id = :id',
            ['token' => password_hash($token, PASSWORD_DEFAULT), 'id' => $user['id']]
        );

        $resetUrl = APP_URL . '/reset-password?token=' . $token . '&email=' . urlencode($email);
        error_log('Password reset link: ' . $resetUrl);

        echo json_encode(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function resetPassword(array $params = []): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid security token.']);
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $tokenStr = $_POST['token'] ?? '';

        if (!$email || !$password || !$tokenStr) {
            http_response_code(400);
            echo json_encode(['error' => 'Email, password, and token are required.']);
            return;
        }

        $db = Database::getInstance();
        $user = $db->fetchOne(
            'SELECT * FROM users WHERE email = :email AND reset_token_expires > NOW()',
            ['email' => $email]
        );

        if (!$user || !password_verify($tokenStr, $user['reset_token'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or expired reset token.']);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->execute(
            'UPDATE users SET password_hash = :hash, reset_token = NULL, reset_token_expires = NULL WHERE id = :id',
            ['hash' => $hash, 'id' => $user['id']]
        );

        echo json_encode(['message' => 'Password has been reset successfully.']);
    }

    public function profile(array $params = []): void
    {
        Auth::requireAuth();
        $user = Auth::currentUser();
        $userModel = new User();
        $userData = $userModel->findById($user['id']);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/profile.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function updateProfile(array $params = []): void
    {
        Auth::requireAuth();

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /profile');
            exit;
        }

        $user = Auth::currentUser();
        $userModel = new User();

        $data = [];
        if (!empty($_POST['name'])) {
            $data['name'] = trim($_POST['name']);
        }
        if (!empty($_POST['phone'])) {
            $data['phone'] = trim($_POST['phone']);
        }

        if (!empty($data)) {
            $userModel->updateProfile($user['id'], $data);
        }

        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            $current = $userModel->findById($user['id']);
            if (password_verify($_POST['current_password'], $current['password_hash'])) {
                $db = Database::getInstance();
                $db->execute(
                    'UPDATE users SET password_hash = :hash WHERE id = :id',
                    ['hash' => password_hash($_POST['new_password'], PASSWORD_DEFAULT), 'id' => $user['id']]
                );
            } else {
                $_SESSION['error'] = 'Current password is incorrect.';
                header('Location: /profile');
                exit;
            }
        }

        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: /profile');
        exit;
    }
}

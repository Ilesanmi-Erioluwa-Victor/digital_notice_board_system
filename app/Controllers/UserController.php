<?php
/**
 * UserController — User management for super admin only.
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * List all users.
     * GET /admin/users — super_admin only
     */
    public function index(array $params = []): void
    {
        Auth::requireAuth(['super_admin']);
        $users = $this->userModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/users.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    /**
     * Update a user's role.
     * POST /admin/users/role/{id} — super_admin only
     */
    public function updateRole(array $params = []): void
    {
        Auth::requireAuth(['super_admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $id   = (int) ($params['id'] ?? 0);
        $role = $_POST['role'] ?? '';

        $allowedRoles = ['super_admin', 'admin', 'viewer'];
        if (!in_array($role, $allowedRoles, true)) {
            $_SESSION['error'] = 'Invalid role specified.';
            header('Location: /admin/users');
            exit;
        }

        $this->userModel->updateRole($id, $role);
        $_SESSION['success'] = 'User role updated successfully.';
        header('Location: /admin/users');
        exit;
    }

    /**
     * Delete a user.
     * POST /admin/users/delete/{id} — super_admin only
     */
    public function delete(array $params = []): void
    {
        Auth::requireAuth(['super_admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $this->userModel->delete($id);
        $_SESSION['success'] = 'User deleted successfully.';
        header('Location: /admin/users');
        exit;
    }
}

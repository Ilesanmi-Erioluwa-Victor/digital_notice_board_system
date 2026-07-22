<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Department;

class UserController
{
    private User $userModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->logModel  = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $users = $this->userModel->all();

        $db = Database::getInstance();
        $faculties = $db->fetchAll('SELECT id, name FROM faculties ORDER BY name');
        $departments = $db->fetchAll('SELECT id, name FROM departments ORDER BY name');
        $programmes = $db->fetchAll('SELECT id, name FROM programmes ORDER BY name');
        $levels = $db->fetchAll('SELECT id, name FROM levels ORDER BY sort_order');

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/users.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function profileForm(array $params = []): void
    {
        Auth::requireAuth();
        $user = Auth::currentUser();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/profile.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $name          = trim($_POST['name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $password      = $_POST['password'] ?? '';
        $role          = $_POST['role'] ?? 'student';
        $departmentId  = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $staffId       = $role === 'staff' ? trim($_POST['staff_id'] ?? '') : null;
        $studentId     = $role === 'student' ? trim($_POST['student_id'] ?? '') : null;

        if (!$name || !$email || !$password) {
            $_SESSION['error'] = 'Name, email, and password are required.';
            header('Location: /admin/users');
            exit;
        }

        $id = $this->userModel->create([
            'name'          => $name,
            'email'         => $email,
            'password'      => $password,
            'role'          => $role,
            'department_id' => $departmentId,
            'staff_id'      => $staffId,
            'student_id'    => $studentId,
        ]);

        $currentUser = Auth::currentUser();
        $this->logModel->log($currentUser['id'], 'created', 'user', $id, 'Created user ' . $name . ' with role ' . $role);

        $_SESSION['success'] = 'User created successfully.';
        header('Location: /admin/users');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /admin/users');
            exit;
        }

        $data = [];
        if (!empty($_POST['name'])) {
            $data['name'] = trim($_POST['name']);
        }
        if (!empty($_POST['email'])) {
            $data['email'] = trim($_POST['email']);
        }
        if (!empty($_POST['role'])) {
            $data['role'] = $_POST['role'];
        }
        if (isset($_POST['department_id']) && $_POST['department_id'] !== '') {
            $data['department_id'] = (int) $_POST['department_id'];
        }
        if (isset($_POST['staff_id'])) {
            $data['staff_id'] = trim($_POST['staff_id']);
        }
        if (isset($_POST['student_id'])) {
            $data['student_id'] = trim($_POST['student_id']);
        }
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $db = Database::getInstance();
            $db->execute('UPDATE users SET password_hash = :hash WHERE id = :id', ['hash' => $hash, 'id' => $id]);
        }

        if (!empty($data)) {
            $this->userModel->updateProfile($id, $data);
        }

        $currentUser = Auth::currentUser();
        $this->logModel->log($currentUser['id'], 'updated', 'user', $id, 'Updated user ID ' . $id);

        $_SESSION['success'] = 'User updated successfully.';
        header('Location: /admin/users');
        exit;
    }

    public function updateRole(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $id   = (int) ($params['id'] ?? 0);
        $role = $_POST['role'] ?? '';

        $allowedRoles = ['admin', 'staff', 'student'];
        if (!in_array($role, $allowedRoles, true)) {
            $_SESSION['error'] = 'Invalid role specified.';
            header('Location: /admin/users');
            exit;
        }

        $this->userModel->updateRole($id, $role);

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'updated_role', 'user', $id, 'Updated user ID ' . $id . ' to role ' . $role);

        $_SESSION['success'] = 'User role updated successfully.';
        header('Location: /admin/users');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/users');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $this->userModel->delete($id);

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'user', $id, 'Deleted user ID ' . $id);

        $_SESSION['success'] = 'User deleted successfully.';
        header('Location: /admin/users');
        exit;
    }

    public function toggleActive(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid security token.']);
            return;
        }

        $id = (int) ($params['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            return;
        }

        $newActive = !$user['is_active'];
        $this->userModel->updateProfile($id, ['is_active' => $newActive]);

        $currentUser = Auth::currentUser();
        $action = $newActive ? 'activated' : 'deactivated';
        $this->logModel->log($currentUser['id'], $action, 'user', $id, $action . ' user ID ' . $id);

        echo json_encode(['success' => true, 'is_active' => $newActive]);
    }

    public function bulkDelete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid security token.']);
            return;
        }

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'No user IDs provided.']);
            return;
        }

        $count = 0;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->userModel->delete($id);
                $count++;
            }
        }

        $currentUser = Auth::currentUser();
        $this->logModel->log($currentUser['id'], 'bulk_deleted', 'user', 0, 'Bulk deleted ' . $count . ' users');

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'deleted' => $count]);
            return;
        }

        $_SESSION['success'] = $count . ' user(s) deleted successfully.';
        header('Location: /admin/users');
        exit;
    }
}

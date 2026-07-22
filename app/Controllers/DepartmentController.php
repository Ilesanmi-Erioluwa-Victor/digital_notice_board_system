<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Department;
use App\Models\ActivityLog;

class DepartmentController
{
    private Department $departmentModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->departmentModel = new Department();
        $this->logModel        = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $departments = $this->departmentModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/departments.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/departments');
            exit;
        }

        $name       = trim($_POST['name'] ?? '');
        $code       = trim($_POST['code'] ?? '');
        $facultyId  = !empty($_POST['faculty_id']) ? (int) $_POST['faculty_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Department name is required.';
            header('Location: /admin/departments');
            exit;
        }

        $id = $this->departmentModel->create($name, $code, $facultyId, $description);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'created', 'department', $id, 'Created department: ' . $name);

        $_SESSION['success'] = 'Department created successfully.';
        header('Location: /admin/departments');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/departments');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $code       = trim($_POST['code'] ?? '');
        $facultyId  = !empty($_POST['faculty_id']) ? (int) $_POST['faculty_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Department name is required.';
            header('Location: /admin/departments');
            exit;
        }

        $this->departmentModel->update($id, [
            'name'        => $name,
            'code'        => $code,
            'faculty_id'  => $facultyId,
            'description' => $description,
        ]);

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'updated', 'department', $id, 'Updated department: ' . $name);

        $_SESSION['success'] = 'Department updated successfully.';
        header('Location: /admin/departments');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/departments');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $dept = $this->departmentModel->findById($id);

        $this->departmentModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'department', $id, 'Deleted department: ' . ($dept['name'] ?? 'ID ' . $id));

        $_SESSION['success'] = 'Department deleted successfully.';
        header('Location: /admin/departments');
        exit;
    }

    public function apiByFaculty(array $params = []): void
    {
        header('Content-Type: application/json');
        $facultyId = (int) ($_GET['faculty_id'] ?? 0);
        if (!$facultyId) {
            echo json_encode([]);
            return;
        }
        $departments = $this->departmentModel->findByFaculty($facultyId);
        echo json_encode($departments);
    }
}

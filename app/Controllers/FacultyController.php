<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Faculty;
use App\Models\ActivityLog;

class FacultyController
{
    private Faculty $facultyModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->facultyModel = new Faculty();
        $this->logModel     = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $faculties = $this->facultyModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/faculties.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/faculties');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Faculty name is required.';
            header('Location: /admin/faculties');
            exit;
        }

        $id = $this->facultyModel->create($name, $code, $description);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'created', 'faculty', $id, 'Created faculty: ' . $name);

        $_SESSION['success'] = 'Faculty created successfully.';
        header('Location: /admin/faculties');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/faculties');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Faculty name is required.';
            header('Location: /admin/faculties');
            exit;
        }

        $this->facultyModel->update($id, [
            'name'        => $name,
            'code'        => $code,
            'description' => $description,
        ]);

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'updated', 'faculty', $id, 'Updated faculty: ' . $name);

        $_SESSION['success'] = 'Faculty updated successfully.';
        header('Location: /admin/faculties');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/faculties');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $faculty = $this->facultyModel->findById($id);

        $this->facultyModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'faculty', $id, 'Deleted faculty: ' . ($faculty['name'] ?? 'ID ' . $id));

        $_SESSION['success'] = 'Faculty deleted successfully.';
        header('Location: /admin/faculties');
        exit;
    }

    public function apiAll(array $params = []): void
    {
        header('Content-Type: application/json');
        $faculties = $this->facultyModel->all();
        echo json_encode($faculties);
    }
}

<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Programme;
use App\Models\ActivityLog;

class ProgrammeController
{
    private Programme $programmeModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->programmeModel = new Programme();
        $this->logModel       = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $programmes = $this->programmeModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/programmes.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/programmes');
            exit;
        }

        $name           = trim($_POST['name'] ?? '');
        $code           = trim($_POST['code'] ?? '');
        $departmentId   = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $durationYears  = !empty($_POST['duration_years']) ? (int) $_POST['duration_years'] : 4;

        if (!$name) {
            $_SESSION['error'] = 'Programme name is required.';
            header('Location: /admin/programmes');
            exit;
        }

        $id = $this->programmeModel->create($name, $code, $departmentId, $durationYears);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'created', 'programme', $id, 'Created programme: ' . $name);

        $_SESSION['success'] = 'Programme created successfully.';
        header('Location: /admin/programmes');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/programmes');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $name           = trim($_POST['name'] ?? '');
        $code           = trim($_POST['code'] ?? '');
        $departmentId   = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $durationYears  = !empty($_POST['duration_years']) ? (int) $_POST['duration_years'] : 4;

        if (!$name) {
            $_SESSION['error'] = 'Programme name is required.';
            header('Location: /admin/programmes');
            exit;
        }

        $this->programmeModel->update($id, [
            'name'           => $name,
            'code'           => $code,
            'department_id'  => $departmentId,
            'duration_years' => $durationYears,
        ]);

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'updated', 'programme', $id, 'Updated programme: ' . $name);

        $_SESSION['success'] = 'Programme updated successfully.';
        header('Location: /admin/programmes');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/programmes');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $prog = $this->programmeModel->findById($id);

        $this->programmeModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'programme', $id, 'Deleted programme: ' . ($prog['name'] ?? 'ID ' . $id));

        $_SESSION['success'] = 'Programme deleted successfully.';
        header('Location: /admin/programmes');
        exit;
    }

    public function apiByDepartment(array $params = []): void
    {
        header('Content-Type: application/json');
        $departmentId = (int) ($_GET['department_id'] ?? 0);
        if (!$departmentId) {
            echo json_encode([]);
            return;
        }
        $programmes = $this->programmeModel->findByDepartment($departmentId);
        echo json_encode($programmes);
    }
}

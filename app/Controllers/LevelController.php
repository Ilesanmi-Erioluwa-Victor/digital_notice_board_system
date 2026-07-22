<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Level;
use App\Models\ActivityLog;

class LevelController
{
    private Level $levelModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->levelModel = new Level();
        $this->logModel   = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $levels = $this->levelModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/levels.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/levels');
            exit;
        }

        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!$name) {
            $_SESSION['error'] = 'Level name is required.';
            header('Location: /admin/levels');
            exit;
        }

        $id = $this->levelModel->create($name, $sortOrder);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'created', 'level', $id, 'Created level: ' . $name);

        $_SESSION['success'] = 'Level created successfully.';
        header('Location: /admin/levels');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/levels');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (!$name) {
            $_SESSION['error'] = 'Level name is required.';
            header('Location: /admin/levels');
            exit;
        }

        $this->levelModel->update($id, $name, $sortOrder);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'updated', 'level', $id, 'Updated level: ' . $name);

        $_SESSION['success'] = 'Level updated successfully.';
        header('Location: /admin/levels');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/levels');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $level = $this->levelModel->findById($id);

        $this->levelModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'level', $id, 'Deleted level: ' . ($level['name'] ?? 'ID ' . $id));

        $_SESSION['success'] = 'Level deleted successfully.';
        header('Location: /admin/levels');
        exit;
    }

    public function apiAll(array $params = []): void
    {
        header('Content-Type: application/json');
        $levels = $this->levelModel->all();
        echo json_encode($levels);
    }
}

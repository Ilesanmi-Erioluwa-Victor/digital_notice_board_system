<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;

class ActivityLogController
{
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLog();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $logs = $this->logModel->getRecent(100);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/logs.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function apiByEntity(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $entityType = $_GET['entity_type'] ?? '';
        $entityId   = (int) ($_GET['entity_id'] ?? 0);

        if (!$entityType || !$entityId) {
            echo json_encode([]);
            return;
        }

        $logs = $this->logModel->getByEntity($entityType, $entityId);
        echo json_encode($logs);
    }

    public function apiRecent(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $limit = isset($_GET['limit']) ? min((int) $_GET['limit'], 100) : 20;
        $logs = $this->logModel->getRecent($limit);
        echo json_encode($logs);
    }
}

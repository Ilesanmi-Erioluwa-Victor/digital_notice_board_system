<?php
/**
 * ActivityLogController — View activity logs (admin only).
 * GET /admin/logs
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Models\ActivityLog;

class ActivityLogController
{
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLog();
    }

    /**
     * Display the activity log page.
     */
    public function index(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);
        $logs = $this->logModel->getRecent(100);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/admin/logs.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}

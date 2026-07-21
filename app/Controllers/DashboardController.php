<?php
/**
 * DashboardController — Admin dashboard with stats and recent activity.
 * GET /admin/dashboard
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;

class DashboardController
{
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLog();
    }

    /**
     * Render the admin dashboard with statistics.
     */
    public function index(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);
        $db = Database::getInstance();

        // Total notices count
        $totalNotices = $db->fetchOne('SELECT COUNT(*) AS count FROM notices');
        $totalNotices = (int) ($totalNotices['count'] ?? 0);

        // Active published notices count
        $activeNotices = $db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE status = 'published'
               AND publish_at <= NOW()
               AND (expires_at IS NULL OR expires_at > NOW())"
        );
        $activeNotices = (int) ($activeNotices['count'] ?? 0);

        // Expired notices count
        $expiredNotices = $db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE expires_at IS NOT NULL AND expires_at <= NOW()"
        );
        $expiredNotices = (int) ($expiredNotices['count'] ?? 0);

        // Draft notices count
        $draftNotices = $db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE status = 'draft'"
        );
        $draftNotices = (int) ($draftNotices['count'] ?? 0);

        // Total categories
        $totalCategories = $db->fetchOne('SELECT COUNT(*) AS count FROM categories');
        $totalCategories = (int) ($totalCategories['count'] ?? 0);

        // Total users
        $totalUsers = $db->fetchOne('SELECT COUNT(*) AS count FROM users');
        $totalUsers = (int) ($totalUsers['count'] ?? 0);

        // Recent activity log
        $recentLogs = $this->logModel->getRecent(10);

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/dashboard.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}

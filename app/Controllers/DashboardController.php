<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Notice;

class DashboardController
{
    private ActivityLog $logModel;
    private Notice $noticeModel;

    public function __construct()
    {
        $this->logModel    = new ActivityLog();
        $this->noticeModel = new Notice();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);

        $db = Database::getInstance();

        $totalNotices = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM notices')['count'] ?? 0);

        $activeNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE status IN ('approved', 'published')
               AND publish_at <= NOW()
               AND (expires_at IS NULL OR expires_at > NOW())"
        )['count'] ?? 0);

        $expiredNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE expires_at IS NOT NULL AND expires_at <= NOW()"
        )['count'] ?? 0);

        $draftNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE status = 'draft'"
        )['count'] ?? 0);

        $pendingNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE approval_status = 'pending'"
        )['count'] ?? 0);

        $totalCategories = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM categories')['count'] ?? 0);

        $totalUsers = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM users')['count'] ?? 0);

        $totalViews = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM notice_views')['count'] ?? 0);

        $recentLogs = $this->logModel->getRecent(10);

        $recentNotices = $db->fetchAll(
            'SELECT n.id, n.title, n.status, n.approval_status, n.created_at, u.name AS author_name
             FROM notices n
             LEFT JOIN users u ON n.posted_by = u.id
             ORDER BY n.created_at DESC
             LIMIT 5'
        );

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/dashboard.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function staff(array $params = []): void
    {
        Auth::requireAuth(['staff', 'admin']);

        $user = Auth::currentUser();
        $db = Database::getInstance();

        $userId = (int) $user['id'];

        $totalNotices = (int) ($db->fetchOne(
            'SELECT COUNT(*) AS count FROM notices WHERE posted_by = :user_id',
            ['user_id' => $userId]
        )['count'] ?? 0);

        $pendingNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE posted_by = :user_id AND approval_status = 'pending'",
            ['user_id' => $userId]
        )['count'] ?? 0);

        $approvedNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE posted_by = :user_id AND approval_status = 'approved'",
            ['user_id' => $userId]
        )['count'] ?? 0);

        $activeNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE posted_by = :user_id
               AND status IN ('approved', 'published')
               AND publish_at <= NOW()
               AND (expires_at IS NULL OR expires_at > NOW())",
            ['user_id' => $userId]
        )['count'] ?? 0);

        $draftNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices WHERE posted_by = :user_id AND status = 'draft'",
            ['user_id' => $userId]
        )['count'] ?? 0);

        $expiredNotices = (int) ($db->fetchOne(
            "SELECT COUNT(*) AS count FROM notices
             WHERE posted_by = :user_id
               AND expires_at IS NOT NULL AND expires_at <= NOW()",
            ['user_id' => $userId]
        )['count'] ?? 0);

        $totalCategories = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM categories')['count'] ?? 0);

        $totalUsers = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM users')['count'] ?? 0);

        $totalViews = (int) ($db->fetchOne('SELECT COUNT(*) AS count FROM notice_views')['count'] ?? 0);

        $recentLogs = $this->logModel->getRecent(10);

        $recentNotices = $db->fetchAll(
            'SELECT n.id, n.title, n.status, n.approval_status, n.created_at, u.name AS author_name
             FROM notices n
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.posted_by = :user_id
             ORDER BY n.created_at DESC
             LIMIT 5',
            ['user_id' => $userId]
        );

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/dashboard.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}

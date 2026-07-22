<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class AnalyticsController
{
    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/analytics.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function apiMonthlyNotices(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT TO_CHAR(created_at, 'YYYY-MM') AS month,
                    COUNT(*) AS total,
                    SUM(CASE WHEN status IN ('approved','published') THEN 1 ELSE 0 END) AS published,
                    SUM(CASE WHEN status = 'pending' OR approval_status = 'pending' THEN 1 ELSE 0 END) AS pending
             FROM notices
             WHERE created_at >= NOW() - INTERVAL '12 months'
             GROUP BY month
             ORDER BY month ASC"
        );

        echo json_encode($rows);
    }

    public function apiCategoryDistribution(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT c.name AS category, COUNT(n.id) AS count
             FROM categories c
             LEFT JOIN notices n ON c.id = n.category_id
             GROUP BY c.id, c.name
             ORDER BY count DESC'
        );

        echo json_encode($rows);
    }

    public function apiMostViewed(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

        $rows = $db->fetchAll(
            'SELECT n.id, n.title, COUNT(nv.id) AS view_count
             FROM notices n
             LEFT JOIN notice_views nv ON n.id = nv.notice_id
             GROUP BY n.id, n.title
             ORDER BY view_count DESC
             LIMIT :limit',
            ['limit' => $limit]
        );

        echo json_encode($rows);
    }

    public function apiStatusBreakdown(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        header('Content-Type: application/json');

        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT status, COUNT(*) AS count
             FROM notices
             GROUP BY status
             ORDER BY count DESC"
        );

        echo json_encode($rows);
    }
}

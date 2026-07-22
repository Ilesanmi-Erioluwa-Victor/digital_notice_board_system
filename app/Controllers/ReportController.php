<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\ActivityLog;

class ReportController
{
    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/reports.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function generate(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/reports');
            exit;
        }

        $type   = $_POST['type'] ?? 'notices';
        $format = $_POST['format'] ?? 'html';
        $from   = $_POST['from_date'] ?? '';
        $to     = $_POST['to_date'] ?? '';

        $db = Database::getInstance();

        if ($type === 'notices') {
            $where = '';
            $params = [];
            if ($from) {
                $where .= ' AND n.created_at >= :from_date';
                $params['from_date'] = $from . ' 00:00:00';
            }
            if ($to) {
                $where .= ' AND n.created_at <= :to_date';
                $params['to_date'] = $to . ' 23:59:59';
            }

            $data = $db->fetchAll(
                'SELECT n.id, n.title, n.priority, n.status, n.approval_status,
                        n.is_pinned, n.created_at, n.publish_at, n.expires_at,
                        c.name AS category_name, u.name AS author_name
                 FROM notices n
                 LEFT JOIN categories c ON n.category_id = c.id
                 LEFT JOIN users u ON n.posted_by = u.id
                 WHERE 1=1 ' . $where . '
                 ORDER BY n.created_at DESC',
                $params
            );
        } elseif ($type === 'users') {
            $data = $db->fetchAll(
                'SELECT id, name, email, role, is_active, created_at
                 FROM users ORDER BY created_at DESC'
            );
        } elseif ($type === 'activity') {
            $data = $db->fetchAll(
                'SELECT al.*, u.name AS user_name
                 FROM activity_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 ORDER BY al.timestamp DESC
                 LIMIT 500'
            );
        } else {
            $_SESSION['error'] = 'Invalid report type.';
            header('Location: /admin/reports');
            exit;
        }

        if ($format === 'csv') {
            $this->outputCsv($type, $data);
            return;
        }

        $this->outputHtml($type, $data, $from, $to);
    }

    private function outputHtml(string $type, array $data, string $from, string $to): void
    {
        $title = ucfirst($type) . ' Report';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($title) ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 2rem; color: #333; }
                h1 { color: #1a1a2e; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; }
                table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
                th, td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem; }
                th { background: #f8fafc; font-weight: 600; color: #475569; }
                tr:hover { background: #f1f5f9; }
                .meta { color: #64748b; font-size: 0.875rem; margin-top: 0.5rem; }
                .no-data { color: #94a3b8; text-align: center; padding: 3rem; }
                @media print { body { margin: 0.5in; } th { background: #f1f5f9; } }
            </style>
        </head>
        <body>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p class="meta">
                Generated: <?= date('Y-m-d H:i:s') ?>
                <?php if ($from): ?> | From: <?= htmlspecialchars($from) ?><?php endif; ?>
                <?php if ($to): ?> | To: <?= htmlspecialchars($to) ?><?php endif; ?>
            </p>
            <?php if (empty($data)): ?>
                <div class="no-data">No data found for the selected criteria.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($data[0]) as $col): ?>
                                <th><?= htmlspecialchars(str_replace('_', ' ', ucfirst($col))) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                    <td><?= htmlspecialchars(is_string($val) || is_numeric($val) ? (string) $val : '') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="meta">Total records: <?= count($data) ?></p>
            <?php endif; ?>
            <p class="meta"><a href="javascript:window.print()">Print / Save as PDF</a></p>
        </body>
        </html>
        <?php
    }

    private function outputCsv(string $type, array $data): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '-report-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}

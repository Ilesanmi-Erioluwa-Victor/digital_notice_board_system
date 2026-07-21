<?php
/**
 * ActivityLog Model — Database interaction for the activity_logs table.
 *
 * Provides an audit trail for all admin actions (create, edit, delete,
 * publish, archive) on notices.
 */

namespace App\Models;

use App\Core\Database;

class ActivityLog
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Record an action in the activity log.
     *
     * @param int|null    $adminId  ID of the admin performing the action
     * @param string      $action   Action type (created, edited, deleted, published, archived)
     * @param int|null    $noticeId ID of the affected notice
     * @param string|null $details  Human-readable description of the action
     * @return int New log entry ID
     */
    public function log(?int $adminId, string $action, ?int $noticeId = null, ?string $details = null): int
    {
        $this->db->execute(
            'INSERT INTO activity_logs (admin_id, action, notice_id, details)
             VALUES (:admin_id, :action, :notice_id, :details)',
            [
                'admin_id'  => $adminId,
                'action'    => $action,
                'notice_id' => $noticeId,
                'details'   => $details,
            ]
        );
        return (int) $this->db->lastInsertId('activity_logs_id_seq');
    }

    /**
     * Retrieve the most recent log entries, joined with user names.
     *
     * @param int $limit Maximum number of entries to return
     * @return array
     */
    public function getRecent(int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT al.*, u.name AS admin_name
             FROM activity_logs al
             LEFT JOIN users u ON al.admin_id = u.id
             ORDER BY al.timestamp DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }
}

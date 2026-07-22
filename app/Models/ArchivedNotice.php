<?php

namespace App\Models;

use App\Core\Database;

class ArchivedNotice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function toggle(int $userId, int $noticeId): array
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM archived_notices WHERE user_id = :user_id AND notice_id = :notice_id',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );

        if ($existing) {
            $this->db->execute(
                'DELETE FROM archived_notices WHERE id = :id',
                ['id' => $existing['id']]
            );
            return ['archived' => false, 'action' => 'unarchived'];
        }

        $this->db->execute(
            'INSERT INTO archived_notices (user_id, notice_id) VALUES (:user_id, :notice_id)',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );
        return ['archived' => true, 'action' => 'archived'];
    }

    public function isArchived(int $userId, int $noticeId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM archived_notices WHERE user_id = :user_id AND notice_id = :notice_id',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );
        return $row !== null;
    }

    public function getArchivedIds(int $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT notice_id FROM archived_notices WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
        return array_column($rows, 'notice_id');
    }

    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, n.title AS notice_title, n.body, n.priority, n.status, n.publish_at, n.created_at,
                    c.name AS category_name
             FROM archived_notices a
             JOIN notices n ON a.notice_id = n.id
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE a.user_id = :user_id
             ORDER BY a.archived_at DESC',
            ['user_id' => $userId]
        );
    }
}
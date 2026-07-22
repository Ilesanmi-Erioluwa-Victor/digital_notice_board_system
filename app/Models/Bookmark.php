<?php

namespace App\Models;

use App\Core\Database;

class Bookmark
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function toggle(int $userId, int $noticeId): array
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM bookmarks WHERE user_id = :user_id AND notice_id = :notice_id',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );

        if ($existing) {
            $this->db->execute(
                'DELETE FROM bookmarks WHERE id = :id',
                ['id' => $existing['id']]
            );
            return ['bookmarked' => false, 'action' => 'removed'];
        }

        $this->db->execute(
            'INSERT INTO bookmarks (user_id, notice_id) VALUES (:user_id, :notice_id)',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );
        return ['bookmarked' => true, 'action' => 'added'];
    }

    public function isBookmarked(int $userId, int $noticeId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM bookmarks WHERE user_id = :user_id AND notice_id = :notice_id',
            ['user_id' => $userId, 'notice_id' => $noticeId]
        );
        return $row !== null;
    }

    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT b.*, n.title AS notice_title, n.body AS notice_body, n.priority, n.status, n.publish_at
             FROM bookmarks b
             JOIN notices n ON b.notice_id = n.id
             WHERE b.user_id = :user_id
             ORDER BY b.created_at DESC',
            ['user_id' => $userId]
        );
    }

    public function getCount(int $noticeId): int
    {
        $row = $this->db->fetchOne(
            'SELECT COUNT(*) AS count FROM bookmarks WHERE notice_id = :notice_id',
            ['notice_id' => $noticeId]
        );
        return (int) ($row['count'] ?? 0);
    }
}

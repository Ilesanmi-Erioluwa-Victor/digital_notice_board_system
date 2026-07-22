<?php

namespace App\Models;

use App\Core\Database;

class NoticeView
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function trackView(int $noticeId, int $userId): int
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM notice_views WHERE notice_id = :notice_id AND user_id = :user_id',
            ['notice_id' => $noticeId, 'user_id' => $userId]
        );

        if ($existing) {
            return $this->db->execute(
                'UPDATE notice_views SET viewed_at = NOW() WHERE id = :id',
                ['id' => $existing['id']]
            );
        }

        $this->db->execute(
            'INSERT INTO notice_views (notice_id, user_id) VALUES (:notice_id, :user_id)',
            ['notice_id' => $noticeId, 'user_id' => $userId]
        );
        return (int) $this->db->lastInsertId('notice_views_id_seq');
    }

    public function isViewed(int $noticeId, int $userId): bool
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM notice_views WHERE notice_id = :notice_id AND user_id = :user_id',
            ['notice_id' => $noticeId, 'user_id' => $userId]
        );
        return $row !== null;
    }

    public function getViewCount(int $noticeId): int
    {
        $row = $this->db->fetchOne(
            'SELECT COUNT(*) AS count FROM notice_views WHERE notice_id = :notice_id',
            ['notice_id' => $noticeId]
        );
        return (int) ($row['count'] ?? 0);
    }

    public function getViewedByUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT nv.*, n.title AS notice_title
             FROM notice_views nv
             JOIN notices n ON nv.notice_id = n.id
             WHERE nv.user_id = :user_id
             ORDER BY nv.viewed_at DESC',
            ['user_id' => $userId]
        );
    }
}

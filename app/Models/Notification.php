<?php

namespace App\Models;

use App\Core\Database;

class Notification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $type, string $title, string $message = '', string $link = ''): int
    {
        $this->db->execute(
            'INSERT INTO notifications (user_id, type, title, message, link)
             VALUES (:user_id, :type, :title, :message, :link)',
            [
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'link'    => $link,
            ]
        );
        return (int) $this->db->lastInsertId('notifications_id_seq');
    }

    public function markAsRead(int $id): int
    {
        return $this->db->execute(
            'UPDATE notifications SET is_read = TRUE WHERE id = :id',
            ['id' => $id]
        );
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->db->execute(
            'UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id AND is_read = FALSE',
            ['user_id' => $userId]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        $row = $this->db->fetchOne(
            'SELECT COUNT(*) AS count FROM notifications WHERE user_id = :user_id AND is_read = FALSE',
            ['user_id' => $userId]
        );
        return (int) ($row['count'] ?? 0);
    }

    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit',
            ['user_id' => $userId, 'limit' => $limit]
        );
    }
}

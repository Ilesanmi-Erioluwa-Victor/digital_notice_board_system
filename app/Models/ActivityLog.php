<?php

namespace App\Models;

use App\Core\Database;

class ActivityLog
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(int $userId, string $action, string $entityType = '', ?int $entityId = null, string $details = '', string $ipAddress = '', string $userAgent = ''): int
    {
        $this->db->execute(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_address, :user_agent)',
            [
                'user_id'     => $userId,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'details'     => $details,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent,
            ]
        );
        return (int) $this->db->lastInsertId('activity_logs_id_seq');
    }

    public function getRecent(int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.timestamp DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }

    public function getByEntity(string $entityType, int $entityId): array
    {
        return $this->db->fetchAll(
            'SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.entity_type = :entity_type AND al.entity_id = :entity_id
             ORDER BY al.timestamp DESC',
            ['entity_type' => $entityType, 'entity_id' => $entityId]
        );
    }
}

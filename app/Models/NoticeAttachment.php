<?php

namespace App\Models;

use App\Core\Database;

class NoticeAttachment
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $noticeId, string $originalName, string $filePath, string $fileType = '', int $fileSize = 0): int
    {
        $this->db->execute(
            'INSERT INTO notice_attachments (notice_id, original_name, file_path, file_type, file_size)
             VALUES (:notice_id, :original_name, :file_path, :file_type, :file_size)',
            [
                'notice_id'     => $noticeId,
                'original_name' => $originalName,
                'file_path'     => $filePath,
                'file_type'     => $fileType,
                'file_size'     => $fileSize,
            ]
        );
        return (int) $this->db->lastInsertId('notice_attachments_id_seq');
    }

    public function findByNoticeId(int $noticeId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM notice_attachments WHERE notice_id = :notice_id ORDER BY uploaded_at DESC',
            ['notice_id' => $noticeId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM notice_attachments WHERE id = :id',
            ['id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM notice_attachments WHERE id = :id',
            ['id' => $id]
        );
    }

    public function getTotalSize(int $noticeId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COALESCE(SUM(file_size), 0) AS total FROM notice_attachments WHERE notice_id = :notice_id',
            ['notice_id' => $noticeId]
        );
        return (int) ($result['total'] ?? 0);
    }
}

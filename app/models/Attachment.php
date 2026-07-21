<?php
/**
 * Attachment Model — Database interaction for the attachments table.
 *
 * Handles file metadata storage, retrieval by notice ID, and deletion.
 * Actual file storage is in /assets/uploads/.
 */

namespace App\Models;

use App\Core\Database;

class Attachment
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new attachment record linked to a notice.
     *
     * @param int    $noticeId
     * @param string $filePath Relative path from project root
     * @param string $fileType MIME type or file extension
     * @return int New attachment ID
     */
    public function create(int $noticeId, string $filePath, string $fileType = ''): int
    {
        $this->db->execute(
            'INSERT INTO attachments (notice_id, file_path, file_type)
             VALUES (:notice_id, :file_path, :file_type)',
            [
                'notice_id' => $noticeId,
                'file_path' => $filePath,
                'file_type' => $fileType,
            ]
        );
        return (int) $this->db->lastInsertId('attachments_id_seq');
    }

    /**
     * Find all attachments for a given notice.
     *
     * @param int $noticeId
     * @return array
     */
    public function findByNoticeId(int $noticeId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM attachments WHERE notice_id = :notice_id ORDER BY uploaded_at DESC',
            ['notice_id' => $noticeId]
        );
    }

    /**
     * Delete an attachment record by ID.
     *
     * @param int $id
     * @return int Affected rows
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM attachments WHERE id = :id',
            ['id' => $id]
        );
    }
}

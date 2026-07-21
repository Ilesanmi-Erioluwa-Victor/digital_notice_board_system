<?php
/**
 * Notice Model — Database interaction for the notices table.
 *
 * The core model handling notice CRUD, active notice queries,
 * search, pagination, and filtering by category.
 */

namespace App\Models;

use App\Core\Database;

class Notice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new notice.
     *
     * @param array $data Associative array with notice fields
     * @return int New notice ID
     */
    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO notices (title, body, category_id, posted_by, priority, status, publish_at, expires_at)
             VALUES (:title, :body, :category_id, :posted_by, :priority, :status, :publish_at, :expires_at)',
            [
                'title'       => $data['title'],
                'body'        => $data['body'],
                'category_id' => $data['category_id'] ?? null,
                'posted_by'   => $data['posted_by'] ?? null,
                'priority'    => $data['priority'] ?? 'normal',
                'status'      => $data['status'] ?? 'draft',
                'publish_at'  => $data['publish_at'] ?? null,
                'expires_at'  => $data['expires_at'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId('notices_id_seq');
    }

    /**
     * Update an existing notice.
     *
     * @param int   $id
     * @param array $data Associative array of fields to update
     * @return int Affected rows
     */
    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['title', 'body', 'category_id', 'priority', 'status', 'publish_at', 'expires_at'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE notices SET ' . implode(', ', $fields) . ' WHERE id = :id';

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete a notice by ID.
     *
     * @param int $id
     * @return int Affected rows
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM notices WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * Find a single notice by ID with related category and author info.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.id = :id',
            ['id' => $id]
        );
    }

    /**
     * Get all currently active notices (published, within schedule).
     * An active notice has status='published', publish_at <= NOW(),
     * and (expires_at IS NULL OR expires_at > NOW()).
     *
     * @return array
     */
    public function getActive(): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.status = \'published\'
               AND n.publish_at <= NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())
             ORDER BY n.priority DESC, n.created_at DESC'
        );
    }

    /**
     * Get notices filtered by category ID.
     *
     * @param int $categoryId
     * @return array
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE n.category_id = :category_id AND n.status = \'published\'
             ORDER BY n.created_at DESC',
            ['category_id' => $categoryId]
        );
    }

    /**
     * Search notices by keyword in title or body.
     * Uses PostgreSQL ILIKE for case-insensitive matching.
     *
     * @param string $keyword
     * @return array
     */
    public function search(string $keyword): array
    {
        $like = '%' . $keyword . '%';
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE n.status = \'published\'
               AND (n.title ILIKE :keyword OR n.body ILIKE :keyword)
             ORDER BY n.created_at DESC',
            ['keyword' => $like]
        );
    }

    /**
     * Get paginated list of notices (optionally filtered).
     *
     * @param int    $page      Current page number (1-indexed)
     * @param int    $perPage   Items per page
     * @param string $statusFilter Optional status filter
     * @param int|null $categoryFilter Optional category ID filter
     * @return array ['notices' => array, 'total' => int, 'pages' => int]
     */
    public function getPaginated(
        int $page = 1,
        int $perPage = 10,
        string $statusFilter = '',
        ?int $categoryFilter = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = ['limit' => $perPage, 'offset' => $offset];

        if ($statusFilter) {
            $where[] = 'n.status = :status';
            $params['status'] = $statusFilter;
        }

        if ($categoryFilter) {
            $where[] = 'n.category_id = :category_id';
            $params['category_id'] = $categoryFilter;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = 'SELECT COUNT(*) AS total FROM notices n ' . $whereClause;
        $countResult = $this->db->fetchOne($countSql, $params);
        $total = (int) ($countResult['total'] ?? 0);

        $dataSql = 'SELECT n.*, c.name AS category_name, u.name AS author_name
                    FROM notices n
                    LEFT JOIN categories c ON n.category_id = c.id
                    LEFT JOIN users u ON n.posted_by = u.id
                    ' . $whereClause . '
                    ORDER BY n.created_at DESC
                    LIMIT :limit OFFSET :offset';

        $notices = $this->db->fetchAll($dataSql, $params);

        return [
            'notices' => $notices,
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'page'    => $page,
        ];
    }

    /**
     * Get the most viewed notices based on a simple view counter.
     * Note: This is a stub — extend with a views column or separate view tracking.
     *
     * @param int $limit
     * @return array
     */
    public function getMostViewed(int $limit = 5): array
    {
        // Returns recently published notices as a proxy for "most viewed"
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE n.status = \'published\'
             ORDER BY n.created_at DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }
}

<?php

namespace App\Models;

use App\Core\Database;

class Notice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO notices (title, body, category_id, posted_by, priority, status, approval_status, approved_by, rejection_reason, is_pinned, target_audience_type, target_ids, publish_at, expires_at)
             VALUES (:title, :body, :category_id, :posted_by, :priority, :status, :approval_status, :approved_by, :rejection_reason, :is_pinned, :target_audience_type, :target_ids, :publish_at, :expires_at)',
            [
                'title'               => $data['title'],
                'body'                => $data['body'],
                'category_id'         => $data['category_id'] ?? null,
                'posted_by'           => $data['posted_by'] ?? null,
                'priority'            => $data['priority'] ?? 'medium',
                'status'              => $data['status'] ?? 'draft',
                'approval_status'     => $data['approval_status'] ?? 'none',
                'approved_by'         => $data['approved_by'] ?? null,
                'rejection_reason'    => $data['rejection_reason'] ?? null,
                'is_pinned'           => $data['is_pinned'] ?? false,
                'target_audience_type' => $data['target_audience_type'] ?? 'everyone',
                'target_ids'          => isset($data['target_ids']) ? '{' . implode(',', array_map('intval', (array)$data['target_ids'])) . '}' : '{}',
                'publish_at'          => $data['publish_at'] ?? null,
                'expires_at'          => $data['expires_at'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId('notices_id_seq');
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['title', 'body', 'category_id', 'priority', 'status', 'approval_status', 'approved_by', 'rejection_reason', 'is_pinned', 'target_audience_type', 'publish_at', 'expires_at'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (array_key_exists('target_ids', $data)) {
            $fields[] = 'target_ids = :target_ids';
            $params['target_ids'] = '{' . implode(',', array_map('intval', (array)$data['target_ids'])) . '}';
        }

        if (empty($fields)) {
            return 0;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE notices SET ' . implode(', ', $fields) . ' WHERE id = :id';

        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM notices WHERE id = :id',
            ['id' => $id]
        );
    }

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

    public function getActive(): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.status IN (\'approved\', \'published\')
               AND n.publish_at <= NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())
             ORDER BY n.is_pinned DESC, n.priority DESC, n.created_at DESC'
        );
    }

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

    public function getPending(): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.status = \'pending\' OR n.approval_status = \'pending\'
             ORDER BY n.created_at ASC'
        );
    }

    public function approve(int $id, int $approvedBy): int
    {
        return $this->db->execute(
            'UPDATE notices SET status = \'approved\', approval_status = \'approved\', approved_by = :approved_by, rejection_reason = NULL, updated_at = NOW()
             WHERE id = :id',
            ['id' => $id, 'approved_by' => $approvedBy]
        );
    }

    public function reject(int $id, int $approvedBy, string $reason): int
    {
        return $this->db->execute(
            'UPDATE notices SET status = \'rejected\', approval_status = \'rejected\', approved_by = :approved_by, rejection_reason = :reason, updated_at = NOW()
             WHERE id = :id',
            ['id' => $id, 'approved_by' => $approvedBy, 'reason' => $reason]
        );
    }

    public function getPinned(): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE n.is_pinned = TRUE
               AND n.status IN (\'approved\', \'published\')
               AND n.publish_at <= NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())
             ORDER BY n.created_at DESC'
        );
    }

    public function getByAudience(string $audienceType, array $targetIds = []): array
    {
        $params = ['audience_type' => $audienceType];
        $targetCondition = '';

        if (!empty($targetIds)) {
            $placeholders = [];
            foreach ($targetIds as $i => $tid) {
                $key = 'tid_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = (int)$tid;
            }
            $targetCondition = ' AND n.target_ids && ARRAY[' . implode(',', $placeholders) . ']';
        }

        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, u.name AS author_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN users u ON n.posted_by = u.id
             WHERE (n.target_audience_type = :audience_type OR n.target_audience_type = \'everyone\')
               AND n.status IN (\'approved\', \'published\')
               AND n.publish_at <= NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())' . $targetCondition . '
             ORDER BY n.is_pinned DESC, n.created_at DESC',
            $params
        );
    }

    public function getUpcomingEvents(int $limit = 10): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE n.status IN (\'approved\', \'published\')
               AND n.publish_at > NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())
             ORDER BY n.publish_at ASC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }

    public function duplicate(int $id): int
    {
        $original = $this->findById($id);
        if (!$original) {
            return 0;
        }

        return $this->create([
            'title'               => $original['title'] . ' (Copy)',
            'body'                => $original['body'],
            'category_id'         => $original['category_id'],
            'posted_by'           => $original['posted_by'],
            'priority'            => $original['priority'],
            'status'              => 'draft',
            'approval_status'     => 'none',
            'is_pinned'           => false,
            'target_audience_type' => $original['target_audience_type'],
            'target_ids'          => $original['target_ids'],
            'publish_at'          => null,
            'expires_at'          => null,
        ]);
    }

    public function search(string $keyword): array
    {
        $like = '%' . $keyword . '%';
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             WHERE n.status IN (\'approved\', \'published\')
               AND (n.title ILIKE :keyword OR n.body ILIKE :keyword)
             ORDER BY n.is_pinned DESC, n.created_at DESC',
            ['keyword' => $like]
        );
    }

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

        $countParams = array_diff_key($params, ['limit' => true, 'offset' => true]);
        $countSql = 'SELECT COUNT(*) AS total FROM notices n ' . $whereClause;
        $countResult = $this->db->fetchOne($countSql, $countParams);
        $total = (int) ($countResult['total'] ?? 0);

        $dataSql = 'SELECT n.*, c.name AS category_name, u.name AS author_name
                    FROM notices n
                    LEFT JOIN categories c ON n.category_id = c.id
                    LEFT JOIN users u ON n.posted_by = u.id
                    ' . $whereClause . '
                    ORDER BY n.is_pinned DESC, n.created_at DESC
                    LIMIT :limit OFFSET :offset';

        $notices = $this->db->fetchAll($dataSql, $params);

        return [
            'notices' => $notices,
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'page'    => $page,
        ];
    }

    public function getMostViewed(int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT n.*, c.name AS category_name, COUNT(nv.id) AS view_count
             FROM notices n
             LEFT JOIN categories c ON n.category_id = c.id
             LEFT JOIN notice_views nv ON n.id = nv.notice_id
             WHERE n.status IN (\'approved\', \'published\')
               AND n.publish_at <= NOW()
               AND (n.expires_at IS NULL OR n.expires_at > NOW())
             GROUP BY n.id, c.name
             ORDER BY view_count DESC, n.is_pinned DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }
}

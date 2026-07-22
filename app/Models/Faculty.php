<?php

namespace App\Models;

use App\Core\Database;

class Faculty
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $code = '', string $description = ''): int
    {
        $this->db->execute(
            'INSERT INTO faculties (name, code, description) VALUES (:name, :code, :description)',
            ['name' => $name, 'code' => $code ?: null, 'description' => $description]
        );
        return (int) $this->db->lastInsertId('faculties_id_seq');
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM faculties ORDER BY name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM faculties WHERE id = :id',
            ['id' => $id]
        );
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['name', 'code', 'description'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        $sql = 'UPDATE faculties SET ' . implode(', ', $fields) . ' WHERE id = :id';
        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM faculties WHERE id = :id',
            ['id' => $id]
        );
    }
}

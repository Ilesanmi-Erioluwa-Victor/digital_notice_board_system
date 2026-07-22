<?php

namespace App\Models;

use App\Core\Database;

class Programme
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $code = '', ?int $departmentId = null, int $durationYears = 4): int
    {
        $this->db->execute(
            'INSERT INTO programmes (name, code, department_id, duration_years) VALUES (:name, :code, :department_id, :duration_years)',
            [
                'name'           => $name,
                'code'           => $code ?: null,
                'department_id'  => $departmentId,
                'duration_years' => $durationYears,
            ]
        );
        return (int) $this->db->lastInsertId('programmes_id_seq');
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, d.name AS department_name
             FROM programmes p
             LEFT JOIN departments d ON p.department_id = d.id
             ORDER BY p.name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT p.*, d.name AS department_name
             FROM programmes p
             LEFT JOIN departments d ON p.department_id = d.id
             WHERE p.id = :id',
            ['id' => $id]
        );
    }

    public function findByDepartment(int $departmentId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM programmes WHERE department_id = :department_id ORDER BY name ASC',
            ['department_id' => $departmentId]
        );
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['name', 'code', 'department_id', 'duration_years'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        $sql = 'UPDATE programmes SET ' . implode(', ', $fields) . ' WHERE id = :id';
        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM programmes WHERE id = :id',
            ['id' => $id]
        );
    }
}

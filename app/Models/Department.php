<?php

namespace App\Models;

use App\Core\Database;

class Department
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $code = '', ?int $facultyId = null, string $description = ''): int
    {
        $this->db->execute(
            'INSERT INTO departments (name, code, faculty_id, description) VALUES (:name, :code, :faculty_id, :description)',
            [
                'name'        => $name,
                'code'        => $code ?: null,
                'faculty_id'  => $facultyId,
                'description' => $description,
            ]
        );
        return (int) $this->db->lastInsertId('departments_id_seq');
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT d.*, f.name AS faculty_name
             FROM departments d
             LEFT JOIN faculties f ON d.faculty_id = f.id
             ORDER BY d.name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT d.*, f.name AS faculty_name
             FROM departments d
             LEFT JOIN faculties f ON d.faculty_id = f.id
             WHERE d.id = :id',
            ['id' => $id]
        );
    }

    public function findByFaculty(int $facultyId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM departments WHERE faculty_id = :faculty_id ORDER BY name ASC',
            ['faculty_id' => $facultyId]
        );
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['name', 'code', 'faculty_id', 'description'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        $sql = 'UPDATE departments SET ' . implode(', ', $fields) . ' WHERE id = :id';
        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM departments WHERE id = :id',
            ['id' => $id]
        );
    }
}

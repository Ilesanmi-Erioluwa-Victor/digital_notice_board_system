<?php

namespace App\Models;

use App\Core\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db->execute(
            'INSERT INTO users (name, email, password_hash, role, staff_id, student_id, department_id, programme_id, level_id, is_active, avatar_url, phone)
             VALUES (:name, :email, :hash, :role, :staff_id, :student_id, :department_id, :programme_id, :level_id, :is_active, :avatar_url, :phone)',
            [
                'name'           => $data['name'],
                'email'          => $data['email'],
                'hash'           => $hash,
                'role'           => $data['role'] ?? 'student',
                'staff_id'       => $data['staff_id'] ?? null,
                'student_id'     => $data['student_id'] ?? null,
                'department_id'  => $data['department_id'] ?? null,
                'programme_id'   => $data['programme_id'] ?? null,
                'level_id'       => $data['level_id'] ?? null,
                'is_active'      => $data['is_active'] ?? true,
                'avatar_url'     => $data['avatar_url'] ?? null,
                'phone'          => $data['phone'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId('users_id_seq');
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );
    }

    public function findByRole(string $role): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM users WHERE role = :role ORDER BY name ASC',
            ['role' => $role]
        );
    }

    public function search(string $keyword): array
    {
        $like = '%' . $keyword . '%';
        return $this->db->fetchAll(
            'SELECT * FROM users
             WHERE name ILIKE :keyword
                OR email ILIKE :keyword
                OR staff_id ILIKE :keyword
                OR student_id ILIKE :keyword
                OR phone ILIKE :keyword
             ORDER BY name ASC',
            ['keyword' => $like]
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, email, role, staff_id, student_id, department_id, programme_id, level_id, is_active, avatar_url, phone, created_at
             FROM users ORDER BY created_at DESC'
        );
    }

    public function updateProfile(int $id, array $data): int
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['name', 'email', 'staff_id', 'student_id', 'department_id', 'programme_id', 'level_id', 'is_active', 'avatar_url', 'phone'];
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
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';

        return $this->db->execute($sql, $params);
    }

    public function updateRole(int $id, string $role): int
    {
        return $this->db->execute(
            'UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id',
            ['role' => $role, 'id' => $id]
        );
    }

    public function updateLastLogin(int $id): int
    {
        return $this->db->execute(
            'UPDATE users SET updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM users WHERE id = :id',
            ['id' => $id]
        );
    }
}

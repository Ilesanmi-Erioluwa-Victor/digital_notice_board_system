<?php

namespace App\Models;

use App\Core\Database;

class Role
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $description = ''): int
    {
        $this->db->execute(
            'INSERT INTO roles (name, description) VALUES (:name, :description)',
            ['name' => $name, 'description' => $description]
        );
        return (int) $this->db->lastInsertId('roles_id_seq');
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM roles ORDER BY name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM roles WHERE id = :id',
            ['id' => $id]
        );
    }

    public function findByName(string $name): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM roles WHERE name = :name',
            ['name' => $name]
        );
    }

    public function update(int $id, string $name, string $description = ''): int
    {
        return $this->db->execute(
            'UPDATE roles SET name = :name, description = :description WHERE id = :id',
            ['name' => $name, 'description' => $description, 'id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM roles WHERE id = :id',
            ['id' => $id]
        );
    }
}

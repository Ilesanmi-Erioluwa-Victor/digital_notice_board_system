<?php

namespace App\Models;

use App\Core\Database;

class Level
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, int $sortOrder = 0): int
    {
        $this->db->execute(
            'INSERT INTO levels (name, sort_order) VALUES (:name, :sort_order)',
            ['name' => $name, 'sort_order' => $sortOrder]
        );
        return (int) $this->db->lastInsertId('levels_id_seq');
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM levels ORDER BY sort_order ASC, name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM levels WHERE id = :id',
            ['id' => $id]
        );
    }

    public function update(int $id, string $name, int $sortOrder = 0): int
    {
        return $this->db->execute(
            'UPDATE levels SET name = :name, sort_order = :sort_order WHERE id = :id',
            ['name' => $name, 'sort_order' => $sortOrder, 'id' => $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM levels WHERE id = :id',
            ['id' => $id]
        );
    }
}

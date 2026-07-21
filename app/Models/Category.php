<?php
/**
 * Category Model — Database interaction for the categories table.
 *
 * Provides CRUD operations for notice categories.
 */

namespace App\Models;

use App\Core\Database;

class Category
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new category.
     *
     * @param string $name
     * @param string $description
     * @return int New category ID
     */
    public function create(string $name, string $description = ''): int
    {
        $this->db->execute(
            'INSERT INTO categories (name, description) VALUES (:name, :description)',
            ['name' => $name, 'description' => $description]
        );
        return (int) $this->db->lastInsertId('categories_id_seq');
    }

    /**
     * Retrieve all categories ordered by name.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM categories ORDER BY name ASC'
        );
    }

    /**
     * Find a category by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM categories WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * Update a category's name and description.
     *
     * @param int    $id
     * @param string $name
     * @param string $description
     * @return int Affected rows
     */
    public function update(int $id, string $name, string $description = ''): int
    {
        return $this->db->execute(
            'UPDATE categories SET name = :name, description = :description WHERE id = :id',
            ['name' => $name, 'description' => $description, 'id' => $id]
        );
    }

    /**
     * Delete a category by ID.
     *
     * @param int $id
     * @return int Affected rows
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM categories WHERE id = :id',
            ['id' => $id]
        );
    }
}

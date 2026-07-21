<?php
/**
 * User Model — Database interaction for the users table.
 *
 * Handles user creation, lookup by email/id, listing all users,
 * role updates, and deletion. All queries use prepared statements.
 */

namespace App\Models;

use App\Core\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user with a hashed password.
     *
     * @param string $name
     * @param string $email
     * @param string $password Plain-text password (will be hashed)
     * @param string $role
     * @return int The new user's ID
     */
    public function create(string $name, string $email, string $password, string $role = 'viewer'): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->execute(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)',
            ['name' => $name, 'email' => $email, 'hash' => $hash, 'role' => $role]
        );
        return (int) $this->db->lastInsertId('users_id_seq');
    }

    /**
     * Find a user by email address (used during login).
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );
    }

    /**
     * Find a user by their primary key ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * Retrieve all users, ordered by creation date (newest first).
     *
     * @return array
     */
    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC'
        );
    }

    /**
     * Update a user's role.
     *
     * @param int    $id
     * @param string $role
     * @return int Affected rows
     */
    public function updateRole(int $id, string $role): int
    {
        return $this->db->execute(
            'UPDATE users SET role = :role WHERE id = :id',
            ['role' => $role, 'id' => $id]
        );
    }

    /**
     * Delete a user by ID.
     *
     * @param int $id
     * @return int Affected rows
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            'DELETE FROM users WHERE id = :id',
            ['id' => $id]
        );
    }
}

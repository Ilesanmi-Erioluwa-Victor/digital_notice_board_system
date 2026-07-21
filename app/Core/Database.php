<?php
/**
 * Database — PDO Singleton Wrapper
 *
 * Provides a single shared PDO connection to PostgreSQL using the configuration
 * loaded from config/config.php. All queries must use prepared statements to
 * prevent SQL injection — raw string concatenation is never used.
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    /**
     * Private constructor — creates the PDO connection using config constants.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // In production, log instead of exposing details
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please check configuration.');
        }
    }

    /**
     * Prevent cloning of the singleton instance.
     */
    private function __clone() {}

    /**
     * Get the singleton Database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the underlying PDO connection for executing queries.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Convenience wrapper to execute a prepared SELECT and return all rows.
     *
     * @param string $sql    SQL query with :named or ? placeholders
     * @param array  $params Associative or indexed parameter values
     * @return array         Array of associative arrays (rows)
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a prepared SELECT and return a single row.
     *
     * @param string $sql
     * @param array  $params
     * @return array|null Single row or null if no result
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE statement.
     *
     * @param string $sql
     * @param array  $params
     * @return int Number of affected rows
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Get the last inserted ID from a sequence.
     *
     * @param string $sequenceName Sequence name (e.g., 'users_id_seq')
     * @return string
     */
    public function lastInsertId(string $sequenceName = ''): string
    {
        return $this->connection->lastInsertId($sequenceName);
    }
}

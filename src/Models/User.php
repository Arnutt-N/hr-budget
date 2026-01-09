<?php
/**
 * User Model
 * 
 * User data access and management
 */

namespace App\Models;

use App\Core\Database;

class User
{
    protected static string $table = 'users';

    /**
     * Find user by ID
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM " . self::$table . " WHERE id = ?",
            [$id]
        );
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?array
    {
        return Database::queryOne(
            "SELECT * FROM " . self::$table . " WHERE email = ?",
            [$email]
        );
    }

    /**
     * Get all users
     */
    public static function all(): array
    {
        return Database::query(
            "SELECT id, email, name, role, department, created_at, last_login_at, is_active 
             FROM " . self::$table . " 
             ORDER BY name ASC"
        );
    }

    /**
     * Get paginated users
     */
    public static function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $users = Database::query(
            "SELECT id, email, name, role, department, created_at, last_login_at, is_active 
             FROM " . self::$table . " 
             ORDER BY name ASC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $total = Database::queryOne(
            "SELECT COUNT(*) as count FROM " . self::$table
        );
        
        return [
            'data' => $users,
            'total' => (int) $total['count'],
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total['count'] / $perPage)
        ];
    }

    /**
     * Create new user
     */
    public static function create(array $data): int
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return Database::insert(self::$table, $data);
    }

    /**
     * Update user
     */
    public static function update(int $id, array $data): int
    {
        // Hash password if being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        return Database::update(self::$table, $data, 'id = ?', [$id]);
    }

    /**
     * Delete user
     */
    public static function delete(int $id): int
    {
        return Database::delete(self::$table, 'id = ?', [$id]);
    }

    /**
     * Update last login timestamp
     */
    public static function updateLastLogin(int $id): void
    {
        Database::update(
            self::$table,
            ['last_login_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        );
    }

    /**
     * Check if email exists
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::$table . " WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = Database::queryOne($sql, $params);
        return (int) $result['count'] > 0;
    }

    /**
     * Get users by role
     */
    public static function getByRole(string $role): array
    {
        return Database::query(
            "SELECT id, email, name, role, department 
             FROM " . self::$table . " 
             WHERE role = ? AND is_active = 1 
             ORDER BY name ASC",
            [$role]
        );
    }
}

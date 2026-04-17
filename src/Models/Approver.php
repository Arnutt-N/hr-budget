<?php
/**
 * Approver Model
 * Handles approver assignments and checks
 */

namespace App\Models;

use App\Core\Database;

class Approver
{
    /**
     * Get all approvers
     */
    public static function all(): array
    {
        $sql = "SELECT a.*, u.name as user_name, u.email, o.name_th as org_name
                FROM approvers a
                JOIN users u ON a.user_id = u.id
                JOIN organizations o ON a.org_id = o.id
                ORDER BY o.name_th, u.name";
        
        return Database::query($sql);
    }

    /**
     * Get approvers for a specific organization
     */
    public static function getByOrg(int $orgId): array
    {
        $sql = "SELECT a.*, u.name as user_name, u.email 
                FROM approvers a
                JOIN users u ON a.user_id = u.id
                WHERE a.org_id = ? AND a.is_active = 1";
        
        return Database::query($sql, [$orgId]);
    }

    /**
     * Check if user is an approver for a specific organization
     */
    public static function isApprover(int $userId, int $orgId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM approvers 
                WHERE user_id = ? AND org_id = ? AND is_active = 1";
        
        $result = Database::query($sql, [$userId, $orgId]);
        return ($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Add an approver
     */
    public static function add(int $userId, int $orgId): bool
    {
        // Prevent duplicates
        if (self::isApprover($userId, $orgId)) {
            return true;
        }
        
        return Database::insert('approvers', [
            'user_id' => $userId,
            'org_id' => $orgId,
            'is_active' => 1
        ]);
    }

    /**
     * Remove an approver
     */
    public static function remove(int $id): bool
    {
        return Database::delete('approvers', 'id = ?', [$id]) > 0;
    }
}

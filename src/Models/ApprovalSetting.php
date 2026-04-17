<?php
/**
 * Approval Setting Model
 * Handles global approval configurations
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

class ApprovalSetting
{
    /**
     * Check if approval workflow is enabled for a specific type
     */
    public static function isEnabled(string $key = 'budget_request_approval'): bool
    {
        $sql = "SELECT is_enabled FROM approval_settings WHERE setting_key = ?";
        $result = Database::query($sql, [$key]);
        
        // If not found, default to false (0)
        return !empty($result) && (int)$result[0]['is_enabled'] === 1;
    }
    
    /**
     * Set approval enabled status
     */
    public static function setEnabled(string $key, bool $enabled): bool
    {
        $userId = Auth::id() ?? null;
        
        // Use UPSERT logic (INSERT ... ON DUPLICATE KEY UPDATE)
        $sql = "INSERT INTO approval_settings (setting_key, is_enabled, updated_by, updated_at) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                is_enabled = VALUES(is_enabled), 
                updated_by = VALUES(updated_by), 
                updated_at = VALUES(updated_at)";
                
        return !empty(Database::query($sql, [$key, $enabled ? 1 : 0, $userId]));
    }
}

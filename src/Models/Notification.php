<?php
/**
 * Notification Model
 * Handles system notification creation and retrieval
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

class Notification
{
    /**
     * Create a new notification
     */
    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        return Database::insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get unread notifications for current user
     */
    public static function getUnread(?int $limit = 10): array
    {
        $userId = Auth::id();
        if (!$userId) return [];
        
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT ?";
                
        return Database::query($sql, [$userId, $limit]);
    }
    
    /**
     * Get all notifications for current user (paginated)
     */
    public static function getAll(?int $limit = 20, int $offset = 0): array
    {
        $userId = Auth::id();
        if (!$userId) return [];
        
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
                
        return Database::query($sql, [$userId, $limit, $offset]);
    }

    /**
     * Count unread notifications
     */
    public static function countUnread(): int
    {
        $userId = Auth::id();
        if (!$userId) return 0;
        
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $result = Database::query($sql, [$userId]);
        
        return (int)($result[0]['count'] ?? 0);
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $id): bool
    {
        $userId = Auth::id();
        // Ensure user owns the notification
        return Database::update('notifications', ['is_read' => 1], 'id = ? AND user_id = ?', [$id, $userId]) > 0;
    }

    /**
     * Mark all as read for current user
     */
    public static function markAllAsRead(): bool
    {
        $userId = Auth::id();
        if (!$userId) return false;
        
        return Database::update('notifications', ['is_read' => 1], 'user_id = ?', [$userId]) > 0;
    }
}

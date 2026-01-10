<?php
/**
 * BudgetRequestApproval Model
 * Handles approval workflow logging
 */

namespace App\Models;

use App\Core\Database;

class BudgetRequestApproval
{
    /**
     * Log an action
     */
    public static function log(int $requestId, string $action, int $userId, ?string $note = null): int
    {
        return Database::insert('budget_request_approvals', [
            'budget_request_id' => $requestId,
            'action' => $action,
            'user_id' => $userId,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get logs by request ID
     */
    public static function getByRequestId(int $requestId): array
    {
        $sql = "SELECT bra.*, u.name as user_name 
                FROM budget_request_approvals bra
                LEFT JOIN users u ON bra.user_id = u.id
                WHERE bra.budget_request_id = ?
                ORDER BY bra.created_at DESC";
        
        return Database::query($sql, [$requestId]);
    }
}

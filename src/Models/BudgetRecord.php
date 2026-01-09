<?php
/**
 * Budget Record Model
 * 
 * Handles monthly budget records
 */

namespace App\Models;

use App\Core\Database;

class BudgetRecord
{
    /**
     * Get records for a specific budget
     */
    public static function getByBudget(int $budgetId): array
    {
        $sql = "SELECT br.*, u.name as created_by_name
                FROM budget_records br
                LEFT JOIN users u ON br.created_by = u.id
                WHERE br.budget_id = ?
                ORDER BY br.record_date DESC, br.created_at DESC";
        
        return Database::query($sql, [$budgetId]);
    }

    /**
     * Get latest record for a specific budget
     */
    public static function getLatest(int $budgetId): ?array
    {
        $sql = "SELECT * FROM budget_records 
                WHERE budget_id = ? 
                ORDER BY record_date DESC, created_at DESC 
                LIMIT 1";
        
        $result = Database::query($sql, [$budgetId]);
        return $result[0] ?? null;
    }

    /**
     * Create new record
     */
    public static function create(array $data): int
    {
        return Database::insert('budget_records', [
            'budget_id' => $data['budget_id'],
            'record_date' => $data['record_date'],
            'record_period' => $data['record_period'] ?? 'beginning',
            'transfer_allocation' => $data['transfer_allocation'] ?? 0,
            'spent_amount' => $data['spent_amount'] ?? 0,
            'request_amount' => $data['request_amount'] ?? 0,
            'po_amount' => $data['po_amount'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }
}

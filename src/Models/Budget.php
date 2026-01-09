<?php
/**
 * Budget Model
 * 
 * Handles budget CRUD operations and aggregations
 */

namespace App\Models;

use App\Core\Database;

class Budget
{
    /**
     * Get all budgets with optional fiscal year filter
     */
    public static function all(?int $fiscalYear = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT b.*, 
                       bc.name_th as category_name,
                       bc.code as category_code,
                       u.name as created_by_name,
                       COALESCE(br.transfer_allocation, 0) as transfer_allocation,
                       COALESCE(br.spent_amount, 0) as current_spent,
                       COALESCE(br.request_amount, 0) as request_amount,
                       COALESCE(br.po_amount, 0) as po_amount,
                       COALESCE(q1.q1_spent, 0) as q1_spent,
                       COALESCE(q2.q2_spent, 0) as q2_spent,
                       COALESCE(q3.q3_spent, 0) as q3_spent,
                       COALESCE(q4.q4_spent, 0) as q4_spent
                FROM budgets b
                LEFT JOIN budget_categories bc ON b.category_id = bc.id
                LEFT JOIN users u ON b.created_by = u.id
                LEFT JOIN budget_records br ON br.id = (
                    SELECT id FROM budget_records 
                    WHERE budget_id = b.id 
                    ORDER BY record_date DESC, created_at DESC 
                    LIMIT 1
                )
                LEFT JOIN (
                    SELECT budget_id, SUM(amount) as q1_spent
                    FROM budget_transactions
                    WHERE transaction_type = 'expenditure' 
                    AND MONTH(created_at) IN (10, 11, 12)
                    GROUP BY budget_id
                ) q1 ON q1.budget_id = b.id
                LEFT JOIN (
                    SELECT budget_id, SUM(amount) as q2_spent
                    FROM budget_transactions
                    WHERE transaction_type = 'expenditure' 
                    AND MONTH(created_at) IN (1, 2, 3)
                    GROUP BY budget_id
                ) q2 ON q2.budget_id = b.id
                LEFT JOIN (
                    SELECT budget_id, SUM(amount) as q3_spent
                    FROM budget_transactions
                    WHERE transaction_type = 'expenditure' 
                    AND MONTH(created_at) IN (4, 5, 6)
                    GROUP BY budget_id
                ) q3 ON q3.budget_id = b.id
                LEFT JOIN (
                    SELECT budget_id, SUM(amount) as q4_spent
                    FROM budget_transactions
                    WHERE transaction_type = 'expenditure' 
                    AND MONTH(created_at) IN (7, 8, 9)
                    GROUP BY budget_id
                ) q4 ON q4.budget_id = b.id";
        
        $params = [];
        
        if ($fiscalYear) {
            $sql .= " WHERE b.fiscal_year = ?";
            $params[] = $fiscalYear;
        }
        
        $sql .= " ORDER BY bc.sort_order ASC, b.id DESC LIMIT {$limit} OFFSET {$offset}";
        
        return Database::query($sql, $params);
    }

    /**
     * Count total budgets
     */
    public static function count(?int $fiscalYear = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM budgets";
        $params = [];
        
        if ($fiscalYear) {
            $sql .= " WHERE fiscal_year = ?";
            $params[] = $fiscalYear;
        }
        
        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Find budget by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT b.*, 
                       bc.name_th as category_name,
                       bc.code as category_code,
                       COALESCE(br.transfer_allocation, 0) as transfer_allocation,
                       COALESCE(br.spent_amount, 0) as current_spent,
                       COALESCE(br.request_amount, 0) as request_amount,
                       COALESCE(br.po_amount, 0) as po_amount
                FROM budgets b
                LEFT JOIN budget_categories bc ON b.category_id = bc.id
                LEFT JOIN budget_records br ON br.id = (
                    SELECT id FROM budget_records 
                    WHERE budget_id = b.id 
                    ORDER BY record_date DESC, created_at DESC 
                    LIMIT 1
                )
                WHERE b.id = ?";
        
        $result = Database::query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * Create new budget
     */
    public static function create(array $data): int
    {
        return Database::insert('budgets', [
            'category_id' => $data['category_id'],
            'fiscal_year' => $data['fiscal_year'] ?? 2568,
            'allocated_amount' => $data['allocated_amount'] ?? 0,
            'spent_amount' => $data['spent_amount'] ?? 0,
            'target_amount' => $data['target_amount'] ?? 0,
            'transfer_in' => $data['transfer_in'] ?? 0,
            'transfer_out' => $data['transfer_out'] ?? 0,
            'created_by' => $data['created_by'],
            'status' => $data['status'] ?? 'draft',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update budget
     */
    public static function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowedFields = [
            'category_id', 'fiscal_year', 'allocated_amount', 'spent_amount',
            'target_amount', 'transfer_in', 'transfer_out', 'status', 'notes',
            'approved_by', 'approved_at'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return Database::update('budgets', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete budget
     */
    public static function delete(int $id): bool
    {
        return Database::delete('budgets', 'id = ?', [$id]) > 0;
    }

    /**
     * Get dashboard statistics
     */
    public static function getStats(int $fiscalYear): array
    {
        $sql = "SELECT 
                    COALESCE(SUM(b.allocated_amount), 0) as total_allocated,
                    COALESCE(SUM(latest_br.spent_amount), 0) as total_spent,
                    COALESCE(SUM(latest_br.transfer_allocation), 0) as total_transfer_allocation,
                    COALESCE(SUM(latest_br.request_amount), 0) as total_request_amount,
                    COALESCE(SUM(latest_br.po_amount), 0) as total_po_amount
                FROM budgets b
                LEFT JOIN budget_records latest_br ON latest_br.id = (
                    SELECT id FROM budget_records 
                    WHERE budget_id = b.id 
                    ORDER BY record_date DESC, created_at DESC 
                    LIMIT 1
                )
                WHERE b.fiscal_year = ?";
        
        $result = Database::query($sql, [$fiscalYear]);
        $stats = $result[0] ?? [];
        
        // Calculate derived stats
        $allocated = (float) ($stats['total_allocated'] ?? 0);
        $spent = (float) ($stats['total_spent'] ?? 0);
        $transferAlloc = (float) ($stats['total_transfer_allocation'] ?? 0);
        $request = (float) ($stats['total_request_amount'] ?? 0);
        $po = (float) ($stats['total_po_amount'] ?? 0);
        
        $stats['total_remaining'] = $allocated - $transferAlloc;
        $stats['total_committed'] = $spent + $request + $po;
        $stats['total_remaining_net'] = $stats['total_remaining'] - $stats['total_committed'];
        
        $stats['rate_with_po'] = $allocated > 0 ? round(($spent + $po) / $allocated * 100, 2) : 0;
        $stats['rate_without_po'] = $allocated > 0 ? round($spent / $allocated * 100, 2) : 0;
        
        return $stats;
    }

    /**
     * Get budget breakdown by category
     */
    public static function getByCategory(int $fiscalYear): array
    {
        $sql = "SELECT 
                    bc.id as category_id,
                    bc.name_th as category_name,
                    bc.code as category_code,
                    COALESCE(SUM(b.allocated_amount), 0) as allocated,
                    COALESCE(SUM(latest_br.spent_amount), 0) as spent,
                    COALESCE(SUM(latest_br.po_amount), 0) as po_amount
                FROM budget_categories bc
                LEFT JOIN budgets b ON bc.id = b.category_id AND b.fiscal_year = ?
                LEFT JOIN budget_records latest_br ON latest_br.id = (
                    SELECT id FROM budget_records 
                    WHERE budget_id = b.id 
                    ORDER BY record_date DESC, created_at DESC 
                    LIMIT 1
                )
                WHERE bc.is_active = 1
                GROUP BY bc.id, bc.name_th, bc.code
                ORDER BY bc.sort_order ASC";
        
        return Database::query($sql, [$fiscalYear]);
    }

    /**
     * Get monthly spending trend
     */
    public static function getMonthlyTrend(int $fiscalYear): array
    {
        $months = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
        $target = [8, 16, 25, 33, 41, 50, 58, 66, 75, 83, 91, 100];
        
        $transactions = [];
        
        // Try to get actual spending from transactions (join with budgets for fiscal_year)
        try {
            $sql = "SELECT 
                        MONTH(bt.created_at) as month,
                        SUM(bt.amount) as total
                    FROM budget_transactions bt
                    INNER JOIN budgets b ON bt.budget_id = b.id
                    WHERE b.fiscal_year = ? AND bt.transaction_type = 'expenditure'
                    GROUP BY MONTH(bt.created_at)
                    ORDER BY month";
            
            $transactions = Database::query($sql, [$fiscalYear]);
        } catch (\Exception $e) {
            // Table may not have data yet, use budget-based calculation
            $transactions = [];
        }
        
        // Build actual data array (cumulative)
        $actual = array_fill(0, 12, null);
        $stats = self::getStats($fiscalYear);
        $totalAllocated = (float) $stats['total_allocated'] ?: 1;
        
        if (!empty($transactions)) {
            $cumulative = 0;
            foreach ($transactions as $t) {
                $monthIndex = (int) $t['month'] - 10;
                if ($monthIndex < 0) $monthIndex += 12;
                $cumulative += (float) $t['total'];
                $actual[$monthIndex] = round(($cumulative / $totalAllocated) * 100, 2);
            }
        } else {
            // No transaction data - use spent_amount proportion across current fiscal months
            $currentMonth = (int) date('n');
            $fiscalMonth = $currentMonth >= 10 ? $currentMonth - 10 : $currentMonth + 2;
            $spentPercent = $stats['spent_percent'] ?? 0;
            
            for ($i = 0; $i <= $fiscalMonth; $i++) {
                $actual[$i] = round(($spentPercent / ($fiscalMonth + 1)) * ($i + 1), 2);
            }
        }
        
        return [
            'labels' => $months,
            'target' => $target,
            'actual' => $actual,
        ];
    }
}

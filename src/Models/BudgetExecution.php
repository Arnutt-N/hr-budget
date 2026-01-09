<?php
/**
 * Budget Execution Model (Refactored)
 * 
 * Now uses budget_allocations table instead of deprecated fact_budget_execution.
 * Joins with plans and organizations for structure data.
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class BudgetExecution
{
    /**
     * Get KPI Statistics for a given fiscal year
     */
    public static function getKpiStats(int $fiscalYear, array $filters = []): array
    {
        $sql = "SELECT 
                    SUM(ba.allocated_pba) as total_budget_act,
                    SUM(ba.allocated_received) as total_allocated,
                    SUM(ba.net_budget) as total_net_budget,
                    SUM(ba.disbursed) as total_disbursed,
                    SUM(COALESCE(ba.pending_approval, 0)) as total_request,
                    SUM(ba.po_commitment) as total_po,
                    SUM(ba.disbursed + COALESCE(ba.pending_approval, 0) + ba.po_commitment) as total_spending,
                    SUM(ba.remaining) as total_balance
                FROM budget_allocations ba";
        
        // Join if filters need it
        if (!empty($filters['org_id']) || !empty($filters['plan_name']) || !empty($filters['search'])) {
            $sql .= " LEFT JOIN plans p ON ba.plan_id = p.id";
            $sql .= " LEFT JOIN organizations o ON ba.organization_id = o.id";
        }

        $sql .= " WHERE ba.fiscal_year = ? AND ba.deleted_at IS NULL";
        $params = [$fiscalYear];

        // Add Organization Filter
        if (!empty($filters['org_id'])) {
            $sql .= " AND ba.organization_id = ?";
            $params[] = $filters['org_id'];
        }
        
        // Add Plan Filter
        if (!empty($filters['plan_name'])) {
            $sql .= " AND p.name_th LIKE ?";
            $params[] = '%' . $filters['plan_name'] . '%';
        }
        
        // Add Search
        if (!empty($filters['search'])) {
             $sql .= " AND (p.name_th LIKE ? OR p.code LIKE ?)";
             $params[] = '%' . $filters['search'] . '%';
             $params[] = '%' . $filters['search'] . '%';
        }
                
        $stats = Database::queryOne($sql, $params);
        
        if (!$stats) {
            $stats = [];
        }
        
        // Calculate percentages safely (avoid division by zero)
        $totalNetBudget = (float) ($stats['total_net_budget'] ?? 0);
        $totalDisbursed = (float) ($stats['total_disbursed'] ?? 0);
        $totalSpending = (float) ($stats['total_spending'] ?? 0);
        
        $percentDisbursed = $totalNetBudget > 0 ? ($totalDisbursed / $totalNetBudget) * 100 : 0;
        $percentSpending = $totalNetBudget > 0 ? ($totalSpending / $totalNetBudget) * 100 : 0;
        
        return array_merge($stats ?: [], [
            'total_allocated' => (float)($stats['total_allocated'] ?? 0),
            'total_budget_act' => (float)($stats['total_budget_act'] ?? 0),
            'transfer_change_amount' => 0, // TODO: Calculate from budget_transfers if needed
            'total_net_budget' => $totalNetBudget,
            'total_disbursed' => $totalDisbursed,
            'total_request' => (float)($stats['total_request'] ?? 0),
            'total_po' => (float)($stats['total_po'] ?? 0),
            'total_spending' => $totalSpending,
            'total_balance' => (float)($stats['total_balance'] ?? 0),
            'percent_disbursed' => round($percentDisbursed, 2),
            'percent_spending' => round($percentSpending, 2)
        ]);
    }

    /**
     * Get all execution records with structure data (with pagination)
     */
    public static function getWithStructure(int $fiscalYear, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT 
                    ba.id,
                    ba.fiscal_year,
                    ba.allocated_pba as budget_act_amount,
                    ba.allocated_received as budget_allocated_amount,
                    ba.net_budget as budget_net_balance,
                    ba.disbursed as disbursed_amount,
                    ba.pending_approval as request_amount,
                    ba.po_commitment as po_pending_amount,
                    ba.remaining as balance_amount,
                    ba.status,
                    ba.created_at as record_date,
                    p.name_th as plan_name,
                    p.code as plan_code,
                    bci.name,
                    bc.name_th as category_name,
                    o.id as org_id,
                    o.name_th as org_name,
                    o.abbreviation as org_abbreviation
                FROM budget_allocations ba
                LEFT JOIN plans p ON ba.plan_id = p.id
                LEFT JOIN budget_category_items bci ON ba.item_id = bci.id
                LEFT JOIN budget_categories bc ON ba.category_id = bc.id
                LEFT JOIN organizations o ON ba.organization_id = o.id
                WHERE ba.fiscal_year = ? AND ba.deleted_at IS NULL";
                
        $params = [$fiscalYear];
        
        // Add Organization Filter
        if (!empty($filters['org_id'])) {
            $sql .= " AND ba.organization_id = ?";
            $params[] = $filters['org_id'];
        }
        
        // Add Plan Filter
        if (!empty($filters['plan_name'])) {
            $sql .= " AND p.name_th LIKE ?";
            $params[] = '%' . $filters['plan_name'] . '%';
        }
        
        // Add Item Search
        if (!empty($filters['search'])) {
            $sql .= " AND (bci.name LIKE ? OR p.name_th LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY ba.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return Database::query($sql, $params);
    }
    
    /**
     * Count total records
     */
    public static function count(int $fiscalYear, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM budget_allocations ba
                LEFT JOIN plans p ON ba.plan_id = p.id
                LEFT JOIN budget_category_items bci ON ba.item_id = bci.id
                WHERE ba.fiscal_year = ? AND ba.deleted_at IS NULL";
                
        $params = [$fiscalYear];
        
        // Add same filters as getWithStructure
        if (!empty($filters['org_id'])) {
            $sql .= " AND ba.organization_id = ?";
            $params[] = $filters['org_id'];
        }
        
        if (!empty($filters['plan_name'])) {
            $sql .= " AND p.name_th LIKE ?";
            $params[] = '%' . $filters['plan_name'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (bci.name LIKE ? OR p.name_th LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $result = Database::queryOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get available fiscal years
     */
    public static function getAvailableYears(): array
    {
        $sql = "SELECT DISTINCT fiscal_year 
                FROM budget_allocations 
                WHERE deleted_at IS NULL
                ORDER BY fiscal_year DESC";
        return Database::query($sql);
    }
    
    /**
     * Get distinct record dates for a fiscal year
     * Uses budget_monthly_snapshots or created_at from allocations
     */
    public static function getDistinctRecordDates(int $fiscalYear): array
    {
        // Try monthly snapshots first
        $sql = "SELECT DISTINCT DATE(snapshot_date) as record_date 
                FROM budget_monthly_snapshots bms
                JOIN budget_allocations ba ON bms.allocation_id = ba.id
                WHERE ba.fiscal_year = ?
                ORDER BY record_date DESC";
        
        $results = Database::query($sql, [$fiscalYear]);
        
        if (empty($results)) {
            // Fallback to allocation created dates
            $sql = "SELECT DISTINCT DATE(created_at) as record_date 
                    FROM budget_allocations 
                    WHERE fiscal_year = ? AND deleted_at IS NULL
                    ORDER BY record_date DESC";
            $results = Database::query($sql, [$fiscalYear]);
        }
        
        return $results;
    }
    
    /**
     * Get chart data grouped by organization
     */
    public static function getChartDataByOrg(int $fiscalYear): array
    {
        $sql = "SELECT 
                    o.name_th as org_name,
                    SUM(ba.allocated_received) as allocated,
                    SUM(ba.disbursed) as disbursed,
                    SUM(ba.disbursed + ba.po_commitment) as spending,
                    CASE 
                        WHEN SUM(ba.net_budget) > 0 
                        THEN AVG((ba.disbursed + ba.po_commitment) / ba.net_budget * 100)
                        ELSE 0 
                    END as avg_percent
                FROM budget_allocations ba
                LEFT JOIN plans p ON ba.plan_id = p.id
                LEFT JOIN organizations o ON ba.organization_id = o.id
                WHERE ba.fiscal_year = ? AND ba.deleted_at IS NULL
                GROUP BY o.id, o.name_th
                ORDER BY allocated DESC";
                
        return Database::query($sql, [$fiscalYear]);
    }
}

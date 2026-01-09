<?php
/**
 * BudgetTracking Model
 * 
 * Handles budget tracking data for fiscal years
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class BudgetTracking
{
    protected static string $table = 'budget_trackings';

    /**
     * Get all tracking data for a fiscal year with optional org filter
     */
    public static function getByFiscalYear(int $fiscalYear, ?int $orgId = null): array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE fiscal_year = ?";
        $params = [$fiscalYear];

        if (!is_null($orgId)) {
            $sql .= " AND organization_id = ?";
            $params[] = $orgId;
        } else {
             // If orgId is null, do we want to fetch global (org_id IS NULL) or ALL?
             // Usually for tracking view, if org is selected, fetch that org.
             // If no org selected (Consolidated?), we might want to SUM? 
             // Or fetch rows where organization_id IS NULL (if we use a specific record for central)?
             // For now, let's assume NULL filter means "Fetch rows where organization_id IS NULL" (Central Budget)
             // OR "Fetch ALL rows" if we want to debug?
             // Based on UI "Lazy loading" ... "Organization Filter"
             // If org filter is set, we show that org's data.
             // If NOT set, maybe show Central? or Sum?
             // Let's implement exact match for now.
             $sql .= " AND organization_id IS NULL";
        }

        return Database::query($sql, $params);
    }

    /**
     * Get tracking data keyed by item ID
     */
    public static function getByFiscalYearKeyed(int $fiscalYear, ?int $orgId = null): array
    {
        $rows = self::getByFiscalYear($fiscalYear, $orgId);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['expense_item_id']] = $row;
        }
        return $result;
    }

    /**
     * Update or Insert tracking data (Upsert)
     */
    public static function upsert(int $fiscalYear, int $itemId, array $data, ?int $orgId = null): bool
    {
        $db = Database::getInstance();
        
        // Fetch expense_group_id and expense_type_id from expense_item
        $itemQuery = "SELECT ei.expense_group_id, eg.expense_type_id 
                      FROM expense_items ei 
                      JOIN expense_groups eg ON ei.expense_group_id = eg.id 
                      WHERE ei.id = ?";
        $itemInfo = Database::queryOne($itemQuery, [$itemId]);
        
        if (!$itemInfo) {
            return false; // Invalid expense item ID
        }
        
        $sql = "INSERT INTO " . self::$table . " 
                (fiscal_year, expense_item_id, expense_group_id, expense_type_id, organization_id, allocated, transfer, disbursed, pending, po) 
                VALUES (:year, :item_id, :group_id, :type_id, :org_id, :alloc, :trans, :disb, :pend, :po)
                ON DUPLICATE KEY UPDATE
                allocated = VALUES(allocated),
                transfer = VALUES(transfer),
                disbursed = VALUES(disbursed),
                pending = VALUES(pending),
                po = VALUES(po)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':year' => $fiscalYear,
            ':item_id' => $itemId,
            ':group_id' => $itemInfo['expense_group_id'],
            ':type_id' => $itemInfo['expense_type_id'],
            ':org_id' => $orgId,
            ':alloc' => (float)($data['allocated'] ?? 0),
            ':trans' => (float)($data['transfer'] ?? 0),
            ':disb' => (float)($data['disbursed'] ?? 0),
            ':pend' => (float)($data['pending'] ?? 0),
            ':po' => (float)($data['po'] ?? 0)
        ]);
    }

    /**
     * Bulk upsert multiple tracking items
     */
    public static function bulkUpsert(int $fiscalYear, array $items, ?int $orgId = null): int
    {
        $count = 0;
        foreach ($items as $itemId => $data) {
            if (self::upsert($fiscalYear, (int)$itemId, $data, $orgId)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get summary statistics for a fiscal year
     */
    public static function getSummary(int $fiscalYear, ?int $orgId = null): array
    {
        $sql = "SELECT 
                    SUM(allocated) as total_allocated,
                    SUM(transfer) as total_transfer,
                    SUM(disbursed) as total_disbursed,
                    SUM(pending) as total_pending,
                    SUM(po) as total_po,
                    SUM(allocated + transfer) as total_budget,
                    SUM(disbursed + pending + po) as total_used,
                    SUM((allocated + transfer) - (disbursed + pending + po)) as total_remaining
                FROM " . self::$table . " WHERE fiscal_year = ?";
        
        $params = [$fiscalYear];
        
        if (!is_null($orgId)) {
            $sql .= " AND organization_id = ?";
            $params[] = $orgId;
        } else {
            // Aggregate ALL if no org specified? Or just NULL org? 
            // Usually summary bar might want TOTAL of everything if no filter.
            // But if tracking view shows rows for NULL org, summary should match rows.
            // Let's match rows:
            $sql .= " AND organization_id IS NULL";
        }
        
        return Database::queryOne($sql, $params) ?? [
            'total_allocated' => 0,
            'total_transfer' => 0,
            'total_disbursed' => 0,
            'total_pending' => 0,
            'total_po' => 0,
            'total_budget' => 0,
            'total_used' => 0,
            'total_remaining' => 0
        ];
    }
}

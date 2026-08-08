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
     * Build the WHERE clause identifying a single tracking row.
     *
     * Rows are identified by the natural key (fiscal_year, expense_item_id,
     * organization_id), not by a surrogate id. A null $orgId targets the
     * central budget row (organization_id IS NULL).
     *
     * @return array{0: string, 1: array} [$where, $params]
     */
    private static function naturalKey(int $fiscalYear, int $itemId, ?int $orgId): array
    {
        $where = 'fiscal_year = ? AND expense_item_id = ?';
        $params = [$fiscalYear, $itemId];

        if (is_null($orgId)) {
            $where .= ' AND organization_id IS NULL';
        } else {
            $where .= ' AND organization_id = ?';
            $params[] = $orgId;
        }

        return [$where, $params];
    }

    /**
     * Find a single tracking row by its natural key — the same key upsert()
     * writes against.
     */
    public static function find(int $fiscalYear, int $itemId, ?int $orgId = null): ?array
    {
        [$where, $params] = self::naturalKey($fiscalYear, $itemId, $orgId);

        return Database::queryOne('SELECT * FROM ' . self::$table . ' WHERE ' . $where, $params);
    }

    /**
     * Update or Insert tracking data (Upsert)
     */
    public static function upsert(int $fiscalYear, int $itemId, array $data, ?int $orgId = null): bool
    {
        // Fetch expense_group_id and expense_type_id from expense_item
        $itemQuery = "SELECT ei.expense_group_id, eg.expense_type_id 
                      FROM expense_items ei 
                      JOIN expense_groups eg ON ei.expense_group_id = eg.id 
                      WHERE ei.id = ?";
        $itemInfo = Database::queryOne($itemQuery, [$itemId]);
        
        if (!$itemInfo) {
            return false; // Invalid expense item ID
        }
        
        $amounts = [
            'allocated' => (float)($data['allocated'] ?? 0),
            'transfer'  => (float)($data['transfer'] ?? 0),
            'disbursed' => (float)($data['disbursed'] ?? 0),
            'pending'   => (float)($data['pending'] ?? 0),
            'po'        => (float)($data['po'] ?? 0),
        ];

        // Look the row up explicitly instead of relying on ON DUPLICATE KEY
        // UPDATE. Neither unique key on this table can fire for this write
        // path: unique_tracking(fiscal_year, budget_category_item_id) and
        // uidx_record_item(disbursement_record_id, expense_item_id) both lead
        // with a column this method never sets, so it stays NULL, MySQL treats
        // every NULL as distinct, and the "upsert" silently inserted a
        // duplicate row on every save. DisbursementRecordRepository::
        // upsertTracking() dodges the same keys the same way; if either key is
        // ever fixed, revisit both.
        $existing = self::find($fiscalYear, $itemId, $orgId);

        if ($existing) {
            Database::update(self::$table, $amounts, 'id = ?', [$existing['id']]);

            // rowCount() is 0 when the submitted values equal the stored ones,
            // which is a successful no-op write, not a failure.
            return true;
        }

        return Database::insert(self::$table, array_merge($amounts, [
            'fiscal_year'      => $fiscalYear,
            'expense_item_id'  => $itemId,
            'expense_group_id' => $itemInfo['expense_group_id'],
            'expense_type_id'  => $itemInfo['expense_type_id'],
            'organization_id'  => $orgId,
        ])) > 0;
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
     * Delete a tracking row by its natural key.
     *
     * Returns the number of rows removed (0 when nothing matched).
     */
    public static function delete(int $fiscalYear, int $itemId, ?int $orgId = null): int
    {
        [$where, $params] = self::naturalKey($fiscalYear, $itemId, $orgId);

        return Database::delete(self::$table, $where, $params);
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

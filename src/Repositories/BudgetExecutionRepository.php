<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read-only data access for the budget-execution report.
 *
 * Money figures come from budget_trackings; the dimensional context
 * (activity / project / organization / fiscal_year / record_month) is sourced
 * from the disbursement records + sessions chain — NOT from budget_trackings'
 * own nullable denormalized columns. This mirrors the legacy
 * App\Controllers\BudgetExecutionController query exactly: records/sessions are
 * the source of truth for which activity/org/month a tracking belongs to.
 *
 * All joins are INNER and the SQL avoids MySQL-only functions, so the queries
 * run identically on the SQLite in-memory harness used by the unit tests.
 */
final class BudgetExecutionRepository
{
    private const FROM_JOINED =
        " FROM budget_trackings bt
          INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
          INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id";

    /**
     * One row per (project, activity) with money sums + quarterly disbursed
     * splits. Quarters follow the Thai fiscal year (Q1 = Oct–Dec).
     *
     * @return array<int,array<string,mixed>>
     */
    public function breakdownRows(int $fiscalYear, ?int $orgId): array
    {
        $sql = "SELECT
                    p.id AS project_id,
                    MAX(p.name_th) AS project_name,
                    a.id AS activity_id,
                    MAX(a.name_th) AS activity_name,
                    MAX(o.name_th) AS org_name,
                    COALESCE(SUM(bt.allocated), 0) AS allocated,
                    COALESCE(SUM(bt.transfer), 0)  AS transfer,
                    COALESCE(SUM(bt.disbursed), 0) AS disbursed,
                    COALESCE(SUM(bt.po), 0)        AS po,
                    COALESCE(SUM(bt.pending), 0)   AS pending,
                    COALESCE(SUM(CASE WHEN ds.record_month IN (10,11,12) THEN bt.disbursed ELSE 0 END), 0) AS q1,
                    COALESCE(SUM(CASE WHEN ds.record_month IN (1,2,3)    THEN bt.disbursed ELSE 0 END), 0) AS q2,
                    COALESCE(SUM(CASE WHEN ds.record_month IN (4,5,6)    THEN bt.disbursed ELSE 0 END), 0) AS q3,
                    COALESCE(SUM(CASE WHEN ds.record_month IN (7,8,9)    THEN bt.disbursed ELSE 0 END), 0) AS q4"
            . self::FROM_JOINED
            . " INNER JOIN activities a ON dr.activity_id = a.id
                INNER JOIN projects p ON a.project_id = p.id
                INNER JOIN organizations o ON ds.organization_id = o.id
                WHERE ds.fiscal_year = ?";

        $params = [$fiscalYear];
        if ($orgId !== null) {
            $sql .= " AND ds.organization_id = ?";
            $params[] = $orgId;
        }
        $sql .= " GROUP BY p.id, a.id ORDER BY p.id, a.id";

        return Database::query($sql, $params);
    }

    /**
     * Total allocated per organization, ranked, for the org chart.
     *
     * @return array<int,array<string,mixed>>
     */
    public function orgTotals(int $fiscalYear, ?int $orgId, int $limit): array
    {
        $sql = "SELECT MAX(o.name_th) AS name, COALESCE(SUM(bt.allocated), 0) AS allocated"
            . self::FROM_JOINED
            . " INNER JOIN organizations o ON ds.organization_id = o.id
                WHERE ds.fiscal_year = ?";

        $params = [$fiscalYear];
        if ($orgId !== null) {
            $sql .= " AND ds.organization_id = ?";
            $params[] = $orgId;
        }
        $sql .= " GROUP BY o.id ORDER BY SUM(bt.allocated) DESC LIMIT ?";
        $params[] = $limit;

        return Database::query($sql, $params);
    }

    /**
     * Distinct fiscal years that have any disbursement data, newest first.
     *
     * @return array<int,array<string,mixed>>
     */
    public function availableYears(): array
    {
        return Database::query(
            "SELECT DISTINCT fiscal_year FROM disbursement_sessions
             ORDER BY fiscal_year DESC"
        );
    }
}

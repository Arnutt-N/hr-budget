<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read-only data access for the analytics report.
 *
 * Source-of-truth decision (design v2 §9.1): Feature A/B read budget_trackings
 * DIRECTLY via its own fiscal_year + record_month + organization_id columns —
 * NOT the disbursement chain and NOT budget_transactions — so the yearly,
 * quarterly and monthly views all reconcile (year = Σ quarters = Σ months) and
 * match DashboardService. Feature C reads budget_requests.
 *
 * Every method takes an org-scope fragment array{sql,params} produced by
 * AccessScopeResolver::orgScopeFilter() and injects it into the WHERE clause
 * BEFORE aggregation, so a scoped user can never read another org's totals.
 * The fragment is one of '1=1' (admin/all), '1=0' (deny-all), or
 * '<col> IN (?,..)' — all parameterised, never interpolated user input.
 *
 * Quarters follow the Thai fiscal year (Q1 = Oct–Dec). SQL avoids MySQL-only
 * functions so the queries run identically on the SQLite unit-test harness.
 *
 * @phpstan-type Scope array{sql:string, params:array<int,mixed>}
 */
final class AnalyticsRepository
{
    /** budget = allocated + transfer, both nullable → COALESCE each term. */
    private const BUDGET_SUM = 'COALESCE(SUM(COALESCE(bt.allocated,0)+COALESCE(bt.transfer,0)),0)';
    private const DISBURSED_SUM = 'COALESCE(SUM(COALESCE(bt.disbursed,0)),0)';

    /**
     * Per fiscal year: budget (allocated+transfer) + disbursed, newest first.
     * Optionally bounded to the most recent $limit years.
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array<int,array<string,mixed>>
     */
    public function yearlyComparison(array $scope, int $limit = 5): array
    {
        $sql = "SELECT
                    bt.fiscal_year AS fiscal_year,
                    {$this->budgetSum()} AS budget,
                    {$this->disbursedSum()} AS disbursed
                FROM budget_trackings bt
                WHERE {$scope['sql']}
                GROUP BY bt.fiscal_year
                ORDER BY bt.fiscal_year DESC
                LIMIT ?";

        $params = array_merge($scope['params'], [$limit]);

        return Database::query($sql, $params);
    }

    /**
     * One year split into Thai fiscal quarters: budget + disbursed per quarter.
     * Returns a single row with q1..q4 columns for budget and disbursed.
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array<string,mixed>
     */
    public function quarterlyComparison(int $fiscalYear, array $scope): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN bt.record_month IN (10,11,12) THEN COALESCE(bt.allocated,0)+COALESCE(bt.transfer,0) ELSE 0 END),0) AS budget_q1,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (1,2,3)    THEN COALESCE(bt.allocated,0)+COALESCE(bt.transfer,0) ELSE 0 END),0) AS budget_q2,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (4,5,6)    THEN COALESCE(bt.allocated,0)+COALESCE(bt.transfer,0) ELSE 0 END),0) AS budget_q3,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (7,8,9)    THEN COALESCE(bt.allocated,0)+COALESCE(bt.transfer,0) ELSE 0 END),0) AS budget_q4,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (10,11,12) THEN COALESCE(bt.disbursed,0) ELSE 0 END),0) AS disbursed_q1,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (1,2,3)    THEN COALESCE(bt.disbursed,0) ELSE 0 END),0) AS disbursed_q2,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (4,5,6)    THEN COALESCE(bt.disbursed,0) ELSE 0 END),0) AS disbursed_q3,
                    COALESCE(SUM(CASE WHEN bt.record_month IN (7,8,9)    THEN COALESCE(bt.disbursed,0) ELSE 0 END),0) AS disbursed_q4
                FROM budget_trackings bt
                WHERE bt.fiscal_year = ? AND {$scope['sql']}";

        $params = array_merge([$fiscalYear], $scope['params']);

        return Database::queryOne($sql, $params) ?? [];
    }

    /**
     * One year: budget + disbursed grouped by record_month (1..12). Caller
     * re-orders into the Oct→Sep fiscal sequence and fills missing months.
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array<int,array<string,mixed>>
     */
    public function monthlyComparison(int $fiscalYear, array $scope): array
    {
        $sql = "SELECT
                    bt.record_month AS record_month,
                    {$this->budgetSum()} AS budget,
                    {$this->disbursedSum()} AS disbursed
                FROM budget_trackings bt
                WHERE bt.fiscal_year = ? AND {$scope['sql']}
                GROUP BY bt.record_month";

        $params = array_merge([$fiscalYear], $scope['params']);

        return Database::query($sql, $params);
    }

    /**
     * Forecast inputs: total allocated for the year + disbursed per record_month.
     * Returns ['total_allocated' => float-ish, 'monthly' => rows].
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array{total_allocated: mixed, monthly: array<int,array<string,mixed>>}
     */
    public function yearAllocatedAndMonthlyDisbursed(int $fiscalYear, array $scope): array
    {
        $totalRow = Database::queryOne(
            "SELECT COALESCE(SUM(COALESCE(bt.allocated,0)),0) AS total_allocated
             FROM budget_trackings bt
             WHERE bt.fiscal_year = ? AND {$scope['sql']}",
            array_merge([$fiscalYear], $scope['params'])
        ) ?? ['total_allocated' => 0];

        $monthly = Database::query(
            "SELECT bt.record_month AS record_month, {$this->disbursedSum()} AS disbursed
             FROM budget_trackings bt
             WHERE bt.fiscal_year = ? AND {$scope['sql']}
             GROUP BY bt.record_month",
            array_merge([$fiscalYear], $scope['params'])
        );

        return [
            'total_allocated' => $totalRow['total_allocated'] ?? 0,
            'monthly' => $monthly,
        ];
    }

    /**
     * Grand totals for one year: requested = SUM(total_amount);
     * approved = SUM(total_amount WHERE request_status='approved').
     * NOT joined to organizations so rows with NULL org_id are still counted.
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array<string,mixed>
     */
    public function requestVsApprovedTotals(int $fiscalYear, array $scope): array
    {
        $sql = "SELECT
                    COALESCE(SUM(COALESCE(br.total_amount,0)),0) AS requested,
                    COALESCE(SUM(CASE WHEN br.request_status = 'approved' THEN COALESCE(br.total_amount,0) ELSE 0 END),0) AS approved
                FROM budget_requests br
                WHERE br.fiscal_year = ? AND {$scope['sql']}";

        $params = array_merge([$fiscalYear], $scope['params']);

        return Database::queryOne($sql, $params) ?? ['requested' => 0, 'approved' => 0];
    }

    /**
     * Per-organization requested vs approved breakdown for one year. LEFT JOIN
     * organizations so NULL-org rows survive, bucketed under COALESCE name.
     *
     * @param array{sql:string, params:array<int,mixed>} $scope
     * @return array<int,array<string,mixed>>
     */
    public function requestVsApprovedByOrg(int $fiscalYear, array $scope): array
    {
        $sql = "SELECT
                    COALESCE(o.name_th, 'ไม่ระบุ') AS org_name,
                    COALESCE(SUM(COALESCE(br.total_amount,0)),0) AS requested,
                    COALESCE(SUM(CASE WHEN br.request_status = 'approved' THEN COALESCE(br.total_amount,0) ELSE 0 END),0) AS approved
                FROM budget_requests br
                LEFT JOIN organizations o ON br.org_id = o.id
                WHERE br.fiscal_year = ? AND {$scope['sql']}
                GROUP BY org_name
                ORDER BY requested DESC";

        $params = array_merge([$fiscalYear], $scope['params']);

        return Database::query($sql, $params);
    }

    private function budgetSum(): string
    {
        return self::BUDGET_SUM;
    }

    private function disbursedSum(): string
    {
        return self::DISBURSED_SUM;
    }
}

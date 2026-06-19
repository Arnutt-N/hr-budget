<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AnalyticsRepository;

/**
 * Read-only analytics aggregations for the SPA /analytics page.
 *
 *  - comparison()        budget vs disbursed by year | quarter | month
 *  - forecast()          flat forecast (allocated/12) vs actual disbursed,
 *                        with running-sum cumulatives
 *  - requestVsApproved() requested vs approved totals + approval rate + org breakdown
 *
 * RBAC (design v2 §10, decided SCOPED): every method takes the authenticated
 * $user and builds an org-scope fragment via AccessScopeResolver::orgScopeFilter
 * which is injected into each query's WHERE BEFORE aggregation. budget_trackings
 * scopes on `organization_id`; budget_requests on `org_id`. The returned payload
 * carries a `scope` flag ('all' for admin/has-all, 'subtree' otherwise) so the
 * SPA can show a "scoped to your organization" disclaimer.
 *
 * Business rules live here (not in SQL) so a future approved_amount column can
 * be swapped in one place: forecast = total_allocated / MONTHS_PER_YEAR,
 * cumulative = running sum, approval rate = approved / requested * 100.
 *
 * SQL is kept driver-portable (no MySQL-only functions) so the service is
 * unit-testable against SQLite in-memory like DashboardService.
 */
final class AnalyticsService
{
    private const MONTHS_PER_YEAR = 12;

    private const TRACKINGS_ORG_COLUMN = 'bt.organization_id';
    private const REQUESTS_ORG_COLUMN = 'br.org_id';

    /** Thai month labels in fiscal-year order (Oct → Sep). */
    private const MONTH_LABELS = [
        'ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.',
        'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.',
    ];

    private const QUARTER_LABELS = ['ไตรมาส 1', 'ไตรมาส 2', 'ไตรมาส 3', 'ไตรมาส 4'];

    private const FISCAL_START_MONTH = 10; // October

    public function __construct(
        private readonly AnalyticsRepository $repo = new AnalyticsRepository(),
        private readonly AccessScopeResolver $scopeResolver = new AccessScopeResolver(),
    ) {}

    /**
     * Budget vs disbursed, dimensioned by year | quarter | month.
     *
     * @param array<string,mixed> $user
     * @return array{
     *   dimension:string, scope:string,
     *   rows: array<int,array<string,mixed>>
     * }
     */
    public function comparison(array $user, int $fiscalYear, string $dimension): array
    {
        $scope = $this->trackingsScope($user);

        $rows = match ($dimension) {
            'quarter' => $this->quarterRows($fiscalYear, $scope['filter']),
            'month'   => $this->monthRows($fiscalYear, $scope['filter']),
            default   => $this->yearRows($scope['filter']),
        };

        return [
            'dimension' => $dimension,
            'scope' => $scope['label'],
            'rows' => $rows,
        ];
    }

    /**
     * Flat forecast (total allocated / 12) vs actual disbursed per month, both
     * with running-sum cumulatives, in fiscal-month order (Oct → Sep).
     *
     * @param array<string,mixed> $user
     * @return array{
     *   scope:string, total_allocated:float, labels: list<string>,
     *   forecast_monthly: list<float>, actual_monthly: list<float>,
     *   forecast_cumulative: list<float>, actual_cumulative: list<float>
     * }
     */
    public function forecast(array $user, int $fiscalYear): array
    {
        $scope = $this->trackingsScope($user);

        $data = $this->repo->yearAllocatedAndMonthlyDisbursed($fiscalYear, $scope['filter']);
        $totalAllocated = (float) ($data['total_allocated'] ?? 0);

        $monthlyForecast = $totalAllocated / self::MONTHS_PER_YEAR;
        $forecastMonthly = array_fill(0, self::MONTHS_PER_YEAR, round($monthlyForecast, 2));

        $actualMonthly = $this->bucketByFiscalMonth($data['monthly'], 'disbursed');

        return [
            'scope' => $scope['label'],
            'total_allocated' => $totalAllocated,
            'labels' => self::MONTH_LABELS,
            'forecast_monthly' => $forecastMonthly,
            'actual_monthly' => $actualMonthly,
            'forecast_cumulative' => $this->runningSum($forecastMonthly),
            'actual_cumulative' => $this->runningSum($actualMonthly),
        ];
    }

    /**
     * Requested vs approved totals for one year + approval rate + org breakdown.
     *
     * @param array<string,mixed> $user
     * @return array{
     *   scope:string, fiscal_year:int,
     *   requested:float, approved:float, approval_rate:float,
     *   by_org: array<int,array{org_name:string, requested:float, approved:float}>
     * }
     */
    public function requestVsApproved(array $user, int $fiscalYear): array
    {
        $scope = $this->requestsScope($user);

        $totals = $this->repo->requestVsApprovedTotals($fiscalYear, $scope['filter']);
        $requested = (float) ($totals['requested'] ?? 0);
        $approved = (float) ($totals['approved'] ?? 0);

        $byOrg = array_map(
            static fn (array $row): array => [
                'org_name' => (string) ($row['org_name'] ?? 'ไม่ระบุ'),
                'requested' => (float) ($row['requested'] ?? 0),
                'approved' => (float) ($row['approved'] ?? 0),
            ],
            $this->repo->requestVsApprovedByOrg($fiscalYear, $scope['filter'])
        );

        return [
            'scope' => $scope['label'],
            'fiscal_year' => $fiscalYear,
            'requested' => $requested,
            'approved' => $approved,
            'approval_rate' => $requested > 0 ? round($approved / $requested * 100, 1) : 0.0,
            'by_org' => $byOrg,
        ];
    }

    // ---- dimension builders ------------------------------------------------

    /**
     * @param array{sql:string, params:array<int,mixed>} $filter
     * @return array<int,array{fiscal_year:int, budget:float, disbursed:float}>
     */
    private function yearRows(array $filter): array
    {
        return array_map(
            static fn (array $row): array => [
                'fiscal_year' => (int) $row['fiscal_year'],
                'budget' => (float) $row['budget'],
                'disbursed' => (float) $row['disbursed'],
            ],
            $this->repo->yearlyComparison($filter)
        );
    }

    /**
     * @param array{sql:string, params:array<int,mixed>} $filter
     * @return array<int,array{label:string, budget:float, disbursed:float}>
     */
    private function quarterRows(int $fiscalYear, array $filter): array
    {
        $row = $this->repo->quarterlyComparison($fiscalYear, $filter);

        $rows = [];
        for ($q = 1; $q <= 4; $q++) {
            $rows[] = [
                'label' => self::QUARTER_LABELS[$q - 1],
                'budget' => (float) ($row["budget_q{$q}"] ?? 0),
                'disbursed' => (float) ($row["disbursed_q{$q}"] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * 12 buckets in fiscal-month order (Oct → Sep), filling missing months.
     *
     * @param array{sql:string, params:array<int,mixed>} $filter
     * @return array<int,array{label:string, budget:float, disbursed:float}>
     */
    private function monthRows(int $fiscalYear, array $filter): array
    {
        $monthly = $this->repo->monthlyComparison($fiscalYear, $filter);
        $budget = $this->bucketByFiscalMonth($monthly, 'budget');
        $disbursed = $this->bucketByFiscalMonth($monthly, 'disbursed');

        $rows = [];
        for ($i = 0; $i < self::MONTHS_PER_YEAR; $i++) {
            $rows[] = [
                'label' => self::MONTH_LABELS[$i],
                'budget' => $budget[$i],
                'disbursed' => $disbursed[$i],
            ];
        }

        return $rows;
    }

    // ---- helpers -----------------------------------------------------------

    /**
     * Fold record_month-keyed rows into 12 floats ordered Oct→Sep.
     *
     * @param array<int,array<string,mixed>> $rows
     * @return list<float>
     */
    private function bucketByFiscalMonth(array $rows, string $valueKey): array
    {
        $data = array_fill(0, self::MONTHS_PER_YEAR, 0.0);
        foreach ($rows as $row) {
            $month = isset($row['record_month']) ? (int) $row['record_month'] : 0;
            if ($month < 1 || $month > 12) {
                continue; // NULL / malformed month bucket — excluded
            }
            $index = ($month - self::FISCAL_START_MONTH + 12) % 12;
            $data[$index] += (float) ($row[$valueKey] ?? 0);
        }

        return $data;
    }

    /**
     * @param list<float> $series
     * @return list<float>
     */
    private function runningSum(array $series): array
    {
        $cumulative = [];
        $running = 0.0;
        foreach ($series as $value) {
            $running += $value;
            $cumulative[] = round($running, 2);
        }

        return $cumulative;
    }

    /**
     * Build the org-scope fragment for budget_trackings (organization_id) and
     * the 'all' vs 'subtree' label the SPA shows.
     *
     * @param array<string,mixed> $user
     * @return array{filter: array{sql:string, params:array<int,mixed>}, label:string}
     */
    private function trackingsScope(array $user): array
    {
        return $this->buildScope($user, self::TRACKINGS_ORG_COLUMN);
    }

    /**
     * @param array<string,mixed> $user
     * @return array{filter: array{sql:string, params:array<int,mixed>}, label:string}
     */
    private function requestsScope(array $user): array
    {
        return $this->buildScope($user, self::REQUESTS_ORG_COLUMN);
    }

    /**
     * @param array<string,mixed> $user
     * @return array{filter: array{sql:string, params:array<int,mixed>}, label:string}
     */
    private function buildScope(array $user, string $column): array
    {
        $filter = $this->scopeResolver->orgScopeFilter($user, $column);

        // '1=1' = admin / has-all → unrestricted ('all'); anything else (an
        // IN(...) subtree clause OR the deny-all '1=0') is a scoped view.
        $label = $filter['sql'] === '1=1' ? 'all' : 'subtree';

        return ['filter' => $filter, 'label' => $label];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Services\AnalyticsService;

/**
 * Read-only analytics aggregations over budget_trackings (A/B) and
 * budget_requests (C), with RBAC org-scoping injected via AccessScopeResolver.
 *
 * Extends RbacSqliteTestCase to inherit the roles/permissions/grants/
 * organizations/users schema so scoped-user paths are testable; adds the
 * budget_trackings + budget_requests fixtures the analytics SQL reads from
 * (incl. the record_month column the comparison/forecast queries bucket on).
 */
class AnalyticsServiceTest extends RbacSqliteTestCase
{
    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();

        $this->pdo->exec(
            "CREATE TABLE budget_trackings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER NOT NULL,
                record_month INTEGER,
                organization_id INTEGER,
                allocated REAL,
                transfer REAL,
                disbursed REAL,
                pending REAL,
                po REAL
             );
             CREATE TABLE budget_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER NOT NULL,
                request_title TEXT,
                request_status TEXT,
                total_amount REAL,
                org_id INTEGER,
                created_by INTEGER,
                created_at TEXT
             );"
        );
    }

    /**
     * @param int|null $month  null exercises the COALESCE/NULL-month path
     * @param float|null $allocated null exercises COALESCE(allocated,0)
     */
    private function seedTracking(
        int $fy,
        ?int $month,
        ?int $orgId,
        ?float $allocated,
        ?float $transfer,
        ?float $disbursed
    ): void {
        $this->pdo->prepare(
            "INSERT INTO budget_trackings
                (fiscal_year, record_month, organization_id, allocated, transfer, disbursed)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([$fy, $month, $orgId, $allocated, $transfer, $disbursed]);
    }

    private function seedRequest(int $fy, string $status, float $amount, ?int $orgId): void
    {
        $this->pdo->prepare(
            "INSERT INTO budget_requests (fiscal_year, request_title, request_status, total_amount, org_id)
             VALUES (?, 'คำขอ', ?, ?, ?)"
        )->execute([$fy, $status, $amount, $orgId]);
    }

    // ---------------------------------------------------------------------
    // Comparison
    // ---------------------------------------------------------------------

    /** @test */
    public function comparison_year_sums_budget_and_disbursed_per_year(): void
    {
        // FY 2569: budget = (100+0) + (NULL-allocated 0 + 50 transfer) = 150 ; disbursed = 80 + 20 = 100
        $this->seedTracking(2569, 10, 1, 100.0, 0.0, 80.0);
        $this->seedTracking(2569, 11, 1, null, 50.0, 20.0); // NULL allocated → COALESCE 0
        // FY 2570: budget = 200 ; disbursed = 10
        $this->seedTracking(2570, 10, 1, 200.0, 0.0, 10.0);

        $result = $this->service->comparison($this->makeAdmin(), 2569, 'year');

        $this->assertSame('year', $result['dimension']);
        $byYear = [];
        foreach ($result['rows'] as $row) {
            $byYear[$row['fiscal_year']] = $row;
        }
        $this->assertSame(150.0, $byYear[2569]['budget']);
        $this->assertSame(100.0, $byYear[2569]['disbursed']);
        $this->assertSame(200.0, $byYear[2570]['budget']);
        $this->assertSame(10.0, $byYear[2570]['disbursed']);
    }

    /** @test */
    public function comparison_quarter_buckets_into_thai_fiscal_quarters(): void
    {
        // Q1 = Oct-Dec, Q2 = Jan-Mar, Q3 = Apr-Jun, Q4 = Jul-Sep
        $this->seedTracking(2569, 10, 1, 400.0, 0.0, 40.0); // Q1
        $this->seedTracking(2569, 1, 1, 0.0, 0.0, 20.0);    // Q2
        $this->seedTracking(2569, 5, 1, 0.0, 0.0, 30.0);    // Q3
        $this->seedTracking(2569, 8, 1, 0.0, 0.0, 10.0);    // Q4
        // wrong year excluded
        $this->seedTracking(2570, 10, 1, 999.0, 0.0, 999.0);

        $result = $this->service->comparison($this->makeAdmin(), 2569, 'quarter');

        $this->assertSame('quarter', $result['dimension']);
        $this->assertCount(4, $result['rows']);
        $disbursedByLabel = [];
        foreach ($result['rows'] as $row) {
            $disbursedByLabel[$row['label']] = $row['disbursed'];
        }
        $this->assertSame(40.0, $disbursedByLabel['ไตรมาส 1']);
        $this->assertSame(20.0, $disbursedByLabel['ไตรมาส 2']);
        $this->assertSame(30.0, $disbursedByLabel['ไตรมาส 3']);
        $this->assertSame(10.0, $disbursedByLabel['ไตรมาส 4']);
    }

    /** @test */
    public function comparison_month_returns_twelve_buckets_oct_to_sep(): void
    {
        $this->seedTracking(2569, 10, 1, 1200.0, 0.0, 100.0); // Oct → index 0
        $this->seedTracking(2569, 1, 1, 0.0, 0.0, 30.0);      // Jan → index 3
        $this->seedTracking(2569, 9, 1, 0.0, 0.0, 50.0);      // Sep → index 11

        $result = $this->service->comparison($this->makeAdmin(), 2569, 'month');

        $this->assertSame('month', $result['dimension']);
        $this->assertCount(12, $result['rows']);
        $this->assertSame('ต.ค.', $result['rows'][0]['label']);
        $this->assertSame('ก.ย.', $result['rows'][11]['label']);
        $this->assertSame(100.0, $result['rows'][0]['disbursed']);
        $this->assertSame(0.0, $result['rows'][1]['disbursed']);
        $this->assertSame(30.0, $result['rows'][3]['disbursed']);
        $this->assertSame(50.0, $result['rows'][11]['disbursed']);
    }

    // ---------------------------------------------------------------------
    // Forecast
    // ---------------------------------------------------------------------

    /** @test */
    public function forecast_is_total_allocated_div_twelve_flat_with_cumulatives(): void
    {
        // total allocated = 1200 → forecast 100/month, cumulative 100,200,...,1200
        $this->seedTracking(2569, 10, 1, 1200.0, 0.0, 100.0); // Oct actual 100
        $this->seedTracking(2569, 11, 1, 0.0, 0.0, 50.0);     // Nov actual 50

        $result = $this->service->forecast($this->makeAdmin(), 2569);

        $this->assertSame(1200.0, $result['total_allocated']);
        $this->assertCount(12, $result['forecast_monthly']);
        $this->assertCount(12, $result['actual_monthly']);
        $this->assertCount(12, $result['forecast_cumulative']);
        $this->assertCount(12, $result['actual_cumulative']);

        // flat forecast
        $this->assertSame(100.0, $result['forecast_monthly'][0]);
        $this->assertSame(100.0, $result['forecast_monthly'][11]);
        // forecast cumulative
        $this->assertSame(100.0, $result['forecast_cumulative'][0]);
        $this->assertSame(200.0, $result['forecast_cumulative'][1]);
        $this->assertSame(1200.0, $result['forecast_cumulative'][11]);
        // actual monthly (Oct index 0, Nov index 1)
        $this->assertSame(100.0, $result['actual_monthly'][0]);
        $this->assertSame(50.0, $result['actual_monthly'][1]);
        // actual cumulative
        $this->assertSame(100.0, $result['actual_cumulative'][0]);
        $this->assertSame(150.0, $result['actual_cumulative'][1]);
        $this->assertSame(150.0, $result['actual_cumulative'][11]);
    }

    /** @test */
    public function forecast_with_no_data_returns_zeroed_series(): void
    {
        $result = $this->service->forecast($this->makeAdmin(), 2569);

        $this->assertSame(0.0, $result['total_allocated']);
        $this->assertSame(array_fill(0, 12, 0.0), $result['forecast_monthly']);
        $this->assertSame(array_fill(0, 12, 0.0), $result['actual_cumulative']);
    }

    // ---------------------------------------------------------------------
    // Request vs Approved
    // ---------------------------------------------------------------------

    /** @test */
    public function request_vs_approved_totals_rate_and_null_org_bucketing(): void
    {
        $orgA = $this->makeOrg(null, 0);
        $this->seedRequest(2569, 'approved', 600.0, $orgA);
        $this->seedRequest(2569, 'pending', 300.0, $orgA);
        $this->seedRequest(2569, 'approved', 100.0, null); // NULL org → grand total + 'ไม่ระบุ' bucket
        // wrong year excluded
        $this->seedRequest(2570, 'approved', 9999.0, $orgA);

        $result = $this->service->requestVsApproved($this->makeAdmin(), 2569);

        // grand totals must count the NULL-org row too
        $this->assertSame(1000.0, $result['requested']);
        $this->assertSame(700.0, $result['approved']);
        $this->assertSame(70.0, $result['approval_rate']);

        // breakdown: NULL org bucketed under 'ไม่ระบุ'
        $byOrg = [];
        foreach ($result['by_org'] as $row) {
            $byOrg[$row['org_name']] = $row;
        }
        $this->assertArrayHasKey('ไม่ระบุ', $byOrg);
        $this->assertSame(100.0, $byOrg['ไม่ระบุ']['approved']);
    }

    /** @test */
    public function request_vs_approved_rate_is_zero_when_no_requests(): void
    {
        $result = $this->service->requestVsApproved($this->makeAdmin(), 2569);

        $this->assertSame(0.0, $result['requested']);
        $this->assertSame(0.0, $result['approved']);
        $this->assertSame(0.0, $result['approval_rate']);
    }

    // ---------------------------------------------------------------------
    // Org scope
    // ---------------------------------------------------------------------

    /** @test */
    public function admin_sees_all_orgs_with_scope_all_metadata(): void
    {
        $this->seedTracking(2569, 10, 1, 100.0, 0.0, 50.0);
        $this->seedTracking(2569, 10, 2, 200.0, 0.0, 60.0);

        $result = $this->service->comparison($this->makeAdmin(), 2569, 'year');

        $this->assertSame('all', $result['scope']);
        $byYear = [];
        foreach ($result['rows'] as $row) {
            $byYear[$row['fiscal_year']] = $row;
        }
        $this->assertSame(300.0, $byYear[2569]['budget']); // both orgs summed
    }

    /** @test */
    public function user_without_grants_is_denied_all_and_sees_zero(): void
    {
        $this->seedTracking(2569, 10, 1, 100.0, 0.0, 50.0);
        $this->seedTracking(2569, 10, 2, 200.0, 0.0, 60.0);

        $result = $this->service->comparison($this->makeUser('viewer'), 2569, 'year');

        $this->assertSame('subtree', $result['scope']);
        // deny-all (1=0) → no rows aggregate to anything
        $this->assertSame([], $result['rows']);
    }

    /** @test */
    public function scoped_user_sees_only_their_subtree(): void
    {
        $orgA = $this->makeOrg(null, 0);   // granted root
        $orgChild = $this->makeOrg($orgA, 1); // descendant of A → visible
        $orgOther = $this->makeOrg(null, 0);  // unrelated → hidden

        $this->seedTracking(2569, 10, $orgA, 100.0, 0.0, 10.0);
        $this->seedTracking(2569, 11, $orgChild, 50.0, 0.0, 5.0);
        $this->seedTracking(2569, 10, $orgOther, 999.0, 0.0, 999.0);

        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'viewer', 'organization', $orgA);

        $result = $this->service->comparison($user, 2569, 'year');

        $this->assertSame('subtree', $result['scope']);
        $byYear = [];
        foreach ($result['rows'] as $row) {
            $byYear[$row['fiscal_year']] = $row;
        }
        // only orgA (100) + orgChild (50) = 150; orgOther excluded
        $this->assertSame(150.0, $byYear[2569]['budget']);
        $this->assertSame(15.0, $byYear[2569]['disbursed']);
    }

    /** @test */
    public function request_vs_approved_respects_org_scope_on_requests(): void
    {
        $orgA = $this->makeOrg(null, 0);
        $orgOther = $this->makeOrg(null, 0);
        $this->seedRequest(2569, 'approved', 500.0, $orgA);
        $this->seedRequest(2569, 'approved', 999.0, $orgOther); // outside scope

        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'viewer', 'organization', $orgA);

        $result = $this->service->requestVsApproved($user, 2569);

        $this->assertSame('subtree', $result['scope']);
        $this->assertSame(500.0, $result['requested']);
        $this->assertSame(500.0, $result['approved']);
    }
}

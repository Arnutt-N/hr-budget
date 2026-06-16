<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Services\BudgetExecutionService;
use PHPUnit\Framework\TestCase;

/**
 * Exercises BudgetExecutionService + BudgetExecutionRepository together against
 * an in-memory SQLite database seeded with the full disbursement chain
 * (organizations → projects → activities → sessions → records → trackings).
 */
final class BudgetExecutionServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("CREATE TABLE organizations (id INTEGER PRIMARY KEY, name_th TEXT)");
        $this->pdo->exec("CREATE TABLE projects (id INTEGER PRIMARY KEY, name_th TEXT)");
        $this->pdo->exec("CREATE TABLE activities (id INTEGER PRIMARY KEY, project_id INTEGER, name_th TEXT)");
        $this->pdo->exec("CREATE TABLE disbursement_sessions (
            id INTEGER PRIMARY KEY, organization_id INTEGER, fiscal_year INTEGER, record_month INTEGER
        )");
        $this->pdo->exec("CREATE TABLE disbursement_records (
            id INTEGER PRIMARY KEY, session_id INTEGER, activity_id INTEGER
        )");
        $this->pdo->exec("CREATE TABLE budget_trackings (
            id INTEGER PRIMARY KEY, disbursement_record_id INTEGER,
            allocated REAL, transfer REAL, disbursed REAL, po REAL, pending REAL
        )");

        $this->seed();
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function service(): BudgetExecutionService
    {
        return new BudgetExecutionService();
    }

    /**
     * Two orgs, two projects (project 1 has activities 1+2, project 2 has
     * activity 3), trackings spread across quarters and two fiscal years.
     */
    private function seed(): void
    {
        $this->pdo->exec("INSERT INTO organizations (id, name_th) VALUES
            (1, 'กองการเจ้าหน้าที่'), (2, 'กองคลัง')");
        $this->pdo->exec("INSERT INTO projects (id, name_th) VALUES
            (1, 'โครงการพัฒนาบุคลากร'), (2, 'โครงการสวัสดิการ')");
        $this->pdo->exec("INSERT INTO activities (id, project_id, name_th) VALUES
            (1, 1, 'อบรม'), (2, 1, 'สัมมนา'), (3, 2, 'ตรวจสุขภาพ')");

        // (id, org, fiscal_year, record_month)
        $this->pdo->exec("INSERT INTO disbursement_sessions (id, organization_id, fiscal_year, record_month) VALUES
            (1, 1, 2569, 11),  -- Q1 (Oct-Dec)
            (2, 1, 2569, 2),   -- Q2 (Jan-Mar)
            (3, 2, 2569, 5),   -- Q3 (Apr-Jun)
            (4, 1, 2568, 11)");

        // (id, session, activity)
        $this->pdo->exec("INSERT INTO disbursement_records (id, session_id, activity_id) VALUES
            (1, 1, 1), (2, 2, 2), (3, 3, 3), (4, 4, 1)");

        // (id, record, allocated, transfer, disbursed, po, pending)
        $this->pdo->exec("INSERT INTO budget_trackings
            (id, disbursement_record_id, allocated, transfer, disbursed, po, pending) VALUES
            (1, 1, 1000, 100, 200, 50, 30),
            (2, 2, 2000, 0,   500, 0,  0),
            (3, 3, 800,  0,   300, 20, 10),
            (4, 4, 999,  0,   111, 0,  0)");
    }

    /** @test */
    public function grand_total_stats_equal_sum_of_projects(): void
    {
        $report = $this->service()->report(2569, null);
        $stats = $report['stats'];

        $this->assertSame(3800.0, $stats['allocated']);
        $this->assertSame(100.0, $stats['transfer']);
        $this->assertSame(3900.0, $stats['total_budget']);
        $this->assertSame(1000.0, $stats['disbursed']);
        $this->assertSame(70.0, $stats['po']);
        $this->assertSame(40.0, $stats['pending']);
        $this->assertSame(1110.0, $stats['total_used']);
        $this->assertSame(2790.0, $stats['remaining']);
        $this->assertSame(28.5, $stats['used_percent']);

        // stats really are the sum of the project rows
        $sumAllocated = array_sum(array_column($report['projects'], 'allocated'));
        $this->assertSame($stats['allocated'], $sumAllocated);
    }

    /** @test */
    public function projects_are_sorted_by_allocated_desc_with_nested_activities(): void
    {
        $projects = $this->service()->report(2569, null)['projects'];

        $this->assertCount(2, $projects);
        $this->assertSame('โครงการพัฒนาบุคลากร', $projects[0]['project_name']);
        $this->assertSame(3000.0, $projects[0]['allocated']);
        $this->assertCount(2, $projects[0]['activities']);
        $this->assertSame('โครงการสวัสดิการ', $projects[1]['project_name']);
        $this->assertCount(1, $projects[1]['activities']);
    }

    /** @test */
    public function quarterly_disbursed_is_bucketed_by_fiscal_month(): void
    {
        $project1 = $this->service()->report(2569, null)['projects'][0];

        // activity 1 disbursed 200 in month 11 (Q1); activity 2 disbursed 500 in month 2 (Q2)
        $this->assertSame(200.0, $project1['q1']);
        $this->assertSame(500.0, $project1['q2']);
        $this->assertSame(0.0, $project1['q3']);
        $this->assertSame(0.0, $project1['q4']);
    }

    /** @test */
    public function project_derived_fields_are_correct(): void
    {
        $project2 = $this->service()->report(2569, null)['projects'][1];

        $this->assertSame(800.0, $project2['net_budget']);
        $this->assertSame(330.0, $project2['total_used']); // 300 + 20 + 10
        $this->assertSame(470.0, $project2['balance']);
        $this->assertSame(41.3, $project2['used_percent']);
    }

    /** @test */
    public function category_chart_lists_projects_by_allocated(): void
    {
        $chart = $this->service()->report(2569, null)['category_chart'];

        $this->assertSame(['โครงการพัฒนาบุคลากร', 'โครงการสวัสดิการ'], $chart['labels']);
        $this->assertSame([3000.0, 800.0], $chart['values']);
    }

    /** @test */
    public function org_chart_ranks_organizations_by_allocated(): void
    {
        $chart = $this->service()->report(2569, null)['org_chart'];

        $this->assertSame(['กองการเจ้าหน้าที่', 'กองคลัง'], $chart['labels']);
        $this->assertSame([3000.0, 800.0], $chart['values']);
    }

    /** @test */
    public function org_filter_narrows_the_report(): void
    {
        $report = $this->service()->report(2569, 1);

        $this->assertSame(1, $report['organization_id']);
        $this->assertCount(1, $report['projects']); // org 2's project excluded
        $this->assertSame('โครงการพัฒนาบุคลากร', $report['projects'][0]['project_name']);
        $this->assertSame(3000.0, $report['stats']['allocated']);
    }

    /** @test */
    public function unknown_year_yields_empty_projects_and_zero_stats(): void
    {
        $report = $this->service()->report(2570, null);

        $this->assertSame([], $report['projects']);
        $this->assertSame(0.0, $report['stats']['allocated']);
        $this->assertSame(0.0, $report['stats']['used_percent']);
        $this->assertSame(['labels' => [], 'values' => []], $report['category_chart']);
    }

    /** @test */
    public function available_years_are_distinct_and_descending(): void
    {
        $years = array_map(static fn ($r) => (int) $r['fiscal_year'], $this->service()->availableYears());

        $this->assertSame([2569, 2568], $years);
    }

    /** @test */
    public function export_rows_flatten_projects_into_activity_rows(): void
    {
        $rows = $this->service()->exportRows(2569, null);

        $this->assertCount(3, $rows); // 2 activities in project 1 + 1 in project 2
        // first project (highest allocated) first, activities in insertion order
        $this->assertSame('อบรม', $rows[0]['activity_name']);
        $this->assertSame('โครงการพัฒนาบุคลากร', $rows[0]['project_name']);
        $this->assertSame(1000.0, $rows[0]['allocated']);
        $this->assertArrayHasKey('used_percent', $rows[0]);
    }
}

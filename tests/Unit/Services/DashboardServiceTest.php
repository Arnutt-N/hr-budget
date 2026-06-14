<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Services\DashboardService;

class DashboardServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE budget_trackings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER NOT NULL,
                allocated REAL DEFAULT 0,
                transfer REAL DEFAULT 0,
                disbursed REAL DEFAULT 0,
                pending REAL DEFAULT 0,
                po REAL DEFAULT 0
            )
        ");
        $this->pdo->exec("
            CREATE TABLE budget_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                budget_id INTEGER,
                transaction_type TEXT NOT NULL,
                amount REAL NOT NULL,
                created_by INTEGER,
                created_at TEXT
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function seedTracking(
        int $fy,
        float $allocated,
        float $transfer,
        float $disbursed,
        float $pending,
        float $po,
    ): void {
        $this->pdo->prepare(
            "INSERT INTO budget_trackings (fiscal_year, allocated, transfer, disbursed, pending, po)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([$fy, $allocated, $transfer, $disbursed, $pending, $po]);
    }

    private function seedExpenditure(float $amount, string $createdAt, string $type = 'expenditure'): void
    {
        $this->pdo->prepare(
            "INSERT INTO budget_transactions (budget_id, transaction_type, amount, created_by, created_at)
             VALUES (1, ?, ?, 1, ?)"
        )->execute([$type, $amount, $createdAt]);
    }

    /** @test */
    public function summary_aggregates_totals_and_percent(): void
    {
        $this->seedTracking(2569, 100.0, 0.0, 80.0, 0.0, 0.0);
        $this->seedTracking(2569, 0.0, 100.0, 0.0, 10.0, 10.0);
        // A different fiscal year must be excluded entirely.
        $this->seedTracking(2568, 999.0, 0.0, 999.0, 0.0, 0.0);

        $summary = (new DashboardService())->summary(2569);

        $this->assertSame(100.0, $summary['allocated']);
        $this->assertSame(100.0, $summary['transfer']);
        $this->assertSame(200.0, $summary['total_budget']);
        $this->assertSame(80.0, $summary['disbursed']);
        $this->assertSame(10.0, $summary['pending']);
        $this->assertSame(10.0, $summary['po']);
        $this->assertSame(100.0, $summary['total_used']);
        $this->assertSame(100.0, $summary['remaining']);
        $this->assertSame(50.0, $summary['used_percent']);
    }

    /** @test */
    public function summary_with_no_data_returns_zeros(): void
    {
        $summary = (new DashboardService())->summary(2569);

        $this->assertSame(2569, $summary['fiscal_year']);
        $this->assertSame(0.0, $summary['allocated']);
        $this->assertSame(0.0, $summary['total_budget']);
        $this->assertSame(0.0, $summary['total_used']);
        $this->assertSame(0.0, $summary['remaining']);
        $this->assertSame(0.0, $summary['used_percent']);
    }

    /** @test */
    public function monthly_expenditure_buckets_into_fiscal_months(): void
    {
        // FY 2569 (BE) fiscal window = Oct 2025 → Sep 2026 (CE).
        $this->seedExpenditure(1000.0, '2025-10-15 10:00:00'); // Oct 2025 → index 0
        $this->seedExpenditure(500.0, '2025-10-20 10:00:00');  // Oct 2025 → index 0 (sums to 1500)
        $this->seedExpenditure(300.0, '2026-01-05 10:00:00');  // Jan 2026 → index 3
        $this->seedExpenditure(200.0, '2026-09-30 10:00:00');  // Sep 2026 → index 11
        // Non-expenditure transaction must be ignored.
        $this->seedExpenditure(9999.0, '2025-10-15 10:00:00', 'allocation');
        // Next fiscal year's October (Oct 2026) must be excluded — upper boundary.
        $this->seedExpenditure(7777.0, '2026-10-15 10:00:00');

        $chart = (new DashboardService())->monthlyExpenditure(2569);

        $this->assertCount(12, $chart['labels']);
        $this->assertCount(12, $chart['data']);
        $this->assertSame('ต.ค.', $chart['labels'][0]);
        $this->assertSame('ก.ย.', $chart['labels'][11]);
        $this->assertSame(1500.0, $chart['data'][0]);  // October
        $this->assertSame(0.0, $chart['data'][1]);     // November (empty)
        $this->assertSame(300.0, $chart['data'][3]);   // January
        $this->assertSame(200.0, $chart['data'][11]);  // September
    }

    /** @test */
    public function monthly_expenditure_with_no_data_returns_twelve_zeros(): void
    {
        $chart = (new DashboardService())->monthlyExpenditure(2569);

        $this->assertSame(array_fill(0, 12, 0.0), $chart['data']);
    }
}

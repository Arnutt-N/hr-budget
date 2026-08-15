<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateSalaryScaleDto;
use App\Services\SalaryScaleService;

class SalaryScaleServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE salary_scales (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_category TEXT NOT NULL,
                level_code TEXT NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                min_amount REAL NOT NULL,
                max_amount REAL NOT NULL,
                doc_no TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(string $from = '2025-10-01', ?string $to = null): CreateSalaryScaleDto
    {
        return new CreateSalaryScaleDto(
            employeeCategory: 'civil_servant',
            levelCode: 'ชำนาญการพิเศษ',
            minAmount: 26460.0,
            maxAmount: 59470.0,
            effectiveFrom: $from,
            effectiveTo: $to,
            docNo: null,
        );
    }

    /** @test */
    public function create_as_admin(): void
    {
        $service = new SalaryScaleService();
        $this->assertNotNull($service->create('admin', $this->makeDto()));
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new SalaryScaleService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function overlapping_open_range_is_rejected(): void
    {
        $service = new SalaryScaleService();
        $this->assertNotNull($service->create('admin', $this->makeDto('2025-10-01')));

        // ช่วงใหม่เริ่มทับช่วงเปิดเดิม ⇒ ปฏิเสธ (ต้องปิดเดิมก่อน)
        $this->assertNull($service->create('admin', $this->makeDto('2026-01-01')));
    }

    /** @test */
    public function range_after_closed_previous_is_accepted(): void
    {
        $service = new SalaryScaleService();
        $this->assertNotNull($service->create('admin', $this->makeDto('2025-10-01', '2025-12-31')));
        $this->assertNotNull($service->create('admin', $this->makeDto('2026-01-01')));
    }

    /** @test */
    public function update_enforces_max_not_below_min(): void
    {
        $service = new SalaryScaleService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertFalse($service->update('admin', $id, ['max_amount' => 10000.0]));
        $this->assertTrue($service->update('admin', $id, ['max_amount' => 60000.0]));
    }
}

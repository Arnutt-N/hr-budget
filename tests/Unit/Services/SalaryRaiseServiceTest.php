<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateSalaryRaiseRoundDto;
use App\Services\SalaryRaiseService;

class SalaryRaiseServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE fiscal_years (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                year INTEGER NOT NULL
            )
        ");
        $this->pdo->exec("INSERT INTO fiscal_years (year) VALUES (2569), (2570)");

        $this->pdo->exec("
            CREATE TABLE organizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name_th TEXT NOT NULL
            )
        ");
        $this->pdo->exec("INSERT INTO organizations (name_th) VALUES ('กอง ก'), ('กอง ข'), ('กอง ค')");

        $this->pdo->exec("
            CREATE TABLE salary_raise_rounds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                round_month TEXT NOT NULL,
                round_year_be INTEGER NOT NULL,
                effective_date TEXT NOT NULL,
                fiscal_year_id INTEGER NOT NULL,
                include_in_budget INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE salary_raise_progress (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                round_id INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                completed_at TEXT,
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

    private function roundDto(): CreateSalaryRaiseRoundDto
    {
        return new CreateSalaryRaiseRoundDto(
            roundMonth: 'oct',
            roundYearBe: 2568,
            effectiveDate: '2025-10-01',
            fiscalYearId: 1,
            includeInBudget: false,
        );
    }

    /** @test */
    public function create_round_as_admin(): void
    {
        $service = new SalaryRaiseService();
        $this->assertNotNull($service->create('admin', $this->roundDto()));
    }

    /** @test */
    public function duplicate_month_year_round_fails(): void
    {
        $service = new SalaryRaiseService();
        $this->assertNotNull($service->create('admin', $this->roundDto()));
        $this->assertNull($service->create('admin', $this->roundDto()));
    }

    /** @test */
    public function include_switch_is_a_plain_update(): void
    {
        $service = new SalaryRaiseService();
        $id = $service->create('admin', $this->roundDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->setIncludeInBudget('admin', $id, true));
        $this->assertTrue($service->setIncludeInBudget('admin', $id, false));
        $this->assertFalse($service->setIncludeInBudget('viewer', $id, true));
    }

    /** @test */
    public function mark_progress_upserts_and_stamps_completed_at(): void
    {
        $service = new SalaryRaiseService();
        $id = $service->create('admin', $this->roundDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->markProgress('admin', $id, 1, 'completed', 'กค 0101/2569'));
        $rows = $service->listProgress($id);
        $this->assertCount(1, $rows);
        $this->assertSame('completed', $rows[0]['status']);
        $this->assertNotNull($rows[0]['completed_at']);

        // upsert รอบสอง: กลับเป็น pending ⇒ completed_at ต้องถูกล้าง
        $this->assertTrue($service->markProgress('admin', $id, 1, 'pending', null));
        $rows = $service->listProgress($id);
        $this->assertCount(1, $rows); // ไม่สร้างแถวใหม่
        $this->assertSame('pending', $rows[0]['status']);
        $this->assertNull($rows[0]['completed_at']);
    }

    /** @test */
    public function mark_progress_rejects_bad_status(): void
    {
        $service = new SalaryRaiseService();
        $id = $service->create('admin', $this->roundDto());
        $this->assertNotNull($id);

        $this->assertFalse($service->markProgress('admin', $id, 1, 'done', null));
    }

    /** @test */
    public function seed_progress_creates_pending_for_all_missing_orgs(): void
    {
        $service = new SalaryRaiseService();
        $id = $service->create('admin', $this->roundDto());
        $this->assertNotNull($id);

        $service->markProgress('admin', $id, 1, 'completed', null); // กอง ก มีแล้ว

        $created = $service->seedProgressForAllOrganizations('admin', $id);
        $this->assertSame(2, $created); // เติมเฉพาะ กอง ข/ค

        $rows = $service->listProgress($id);
        $this->assertCount(3, $rows);

        // seed ซ้ำ = ไม่สร้างเพิ่ม
        $this->assertSame(0, $service->seedProgressForAllOrganizations('admin', $id));
    }
}

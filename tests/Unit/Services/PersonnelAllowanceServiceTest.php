<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePersonnelAllowanceDto;
use App\Services\PersonnelAllowanceService;

class PersonnelAllowanceServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE positions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pay_no TEXT NOT NULL,
                employee_category TEXT NOT NULL,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("CREATE TABLE expense_items (id INTEGER PRIMARY KEY AUTOINCREMENT, name_th TEXT)");
        $this->pdo->exec("
            CREATE TABLE allowance_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL,
                name_th TEXT NOT NULL,
                short_name TEXT,
                expense_item_id INTEGER,
                scope TEXT DEFAULT 'position',
                vacant_eligible INTEGER DEFAULT 0,
                report_scope TEXT DEFAULT 'personnel',
                basis TEXT DEFAULT 'flat',
                rate_kind TEXT DEFAULT 'exact',
                budget_basis TEXT DEFAULT 'establishment',
                legal_ref TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE personnel_allowances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                person_id TEXT NOT NULL,
                position_id INTEGER NOT NULL,
                allowance_type_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT,
                doc_date TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");

        $this->pdo->exec("INSERT INTO positions (pay_no, employee_category) VALUES ('1001', 'civil_servant')");
        $this->pdo->exec("INSERT INTO allowance_types (code, name_th) VALUES ('HOUSE_RENT', 'ค่าเช่าบ้าน')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(): CreatePersonnelAllowanceDto
    {
        return new CreatePersonnelAllowanceDto(
            personId: 'P-1001',
            positionId: 1,
            allowanceTypeId: 1,
            amount: 5000.0,
            effectiveFrom: '2025-10-01',
            effectiveTo: null,
            docNo: null,
            docDate: null,
        );
    }

    /** @test */
    public function create_actual_receipt_as_admin(): void
    {
        $service = new PersonnelAllowanceService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $result = $service->list(1, 50, ['person_id' => 'P-1001']);
        $this->assertSame(1, $result['meta']['total']);
        $this->assertSame(5000.0, (float) $result['data'][0]['amount']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new PersonnelAllowanceService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function create_with_missing_position_or_type_fails(): void
    {
        $service = new PersonnelAllowanceService();
        $this->assertNull($service->create('admin', new CreatePersonnelAllowanceDto(
            personId: 'X', positionId: 999, allowanceTypeId: 1, amount: 100.0,
            effectiveFrom: '2025-10-01', effectiveTo: null, docNo: null, docDate: null,
        )));
        $this->assertNull($service->create('admin', new CreatePersonnelAllowanceDto(
            personId: 'X', positionId: 1, allowanceTypeId: 999, amount: 100.0,
            effectiveFrom: '2025-10-01', effectiveTo: null, docNo: null, docDate: null,
        )));
    }

    /** @test */
    public function soft_delete_removes_receipt(): void
    {
        $service = new PersonnelAllowanceService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->delete('admin', $id));
        $this->assertSame(0, $service->list(1, 50, [])['meta']['total']);
    }
}

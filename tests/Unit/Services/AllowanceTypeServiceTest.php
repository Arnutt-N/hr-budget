<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateAllowanceTypeDto;
use App\Services\AllowanceTypeService;

class AllowanceTypeServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

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
        $this->pdo->exec("INSERT INTO allowance_types (code, name_th) VALUES ('PTK', 'พ.ต.ก.')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(string $code = 'NEW_TYPE'): CreateAllowanceTypeDto
    {
        return new CreateAllowanceTypeDto(
            code: $code,
            nameTh: 'เงินเพิ่มใหม่',
            shortName: 'ใหม่',
            expenseItemId: 18,
            scope: 'position',
            vacantEligible: false,
            reportScope: ['personnel'],
            basis: 'flat',
            rateKind: 'exact',
            budgetBasis: 'establishment',
            legalRef: null,
        );
    }

    /** @test */
    public function create_type_as_admin(): void
    {
        $service = new AllowanceTypeService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $row = $service->findById($id);
        $this->assertSame('เงินเพิ่มใหม่', $row['name_th']);
        $this->assertSame('personnel', $row['report_scope']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new AllowanceTypeService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function duplicate_code_is_rejected(): void
    {
        $service = new AllowanceTypeService();
        $this->assertNotNull($service->create('admin', $this->makeDto()));
        $this->assertNull($service->create('admin', $this->makeDto()));
    }

    /** @test */
    public function soft_delete_removes_type(): void
    {
        $service = new AllowanceTypeService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->delete('admin', $id));
        $this->assertNull($service->findById($id));
    }
}

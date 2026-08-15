<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePositionAllowanceDto;
use App\Dtos\UpdatePositionAllowanceDto;
use App\Services\PositionAllowanceService;

class PositionAllowanceServiceTest extends TestCase
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
            CREATE TABLE position_allowances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                allowance_type_id INTEGER NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");

        $this->pdo->exec("INSERT INTO positions (pay_no, employee_category) VALUES ('1001', 'civil_servant')");
        $this->pdo->exec("INSERT INTO allowance_types (code, name_th) VALUES ('PTK', 'พ.ต.ก.')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(int $positionId = 1, int $typeId = 1): CreatePositionAllowanceDto
    {
        return new CreatePositionAllowanceDto(
            positionId: $positionId,
            allowanceTypeId: $typeId,
            effectiveFrom: '2025-10-01',
            effectiveTo: null,
            docNo: null,
        );
    }

    /** @test */
    public function create_entitlement_as_admin(): void
    {
        $service = new PositionAllowanceService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $rows = $service->listByPosition(1);
        $this->assertCount(1, $rows);
        $this->assertSame('พ.ต.ก.', $rows[0]['allowance_name']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new PositionAllowanceService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function create_with_missing_position_or_type_fails(): void
    {
        $service = new PositionAllowanceService();
        $this->assertNull($service->create('admin', $this->makeDto(positionId: 999)));
        $this->assertNull($service->create('admin', $this->makeDto(typeId: 999)));
    }

    /** @test */
    public function list_by_missing_position_returns_null(): void
    {
        $service = new PositionAllowanceService();
        $this->assertNull($service->listByPosition(999));
    }

    /** @test */
    public function soft_delete_removes_entitlement(): void
    {
        $service = new PositionAllowanceService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->delete('admin', 1, $id));
        $this->assertCount(0, $service->listByPosition(1));
    }

    /** @test */
    public function update_and_delete_are_scoped_to_the_position_in_url(): void
    {
        $service = new PositionAllowanceService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        // อัตรา 2 (ยังไม่มีใน test schema — เพิ่ม)
        $this->pdo->exec("INSERT INTO positions (pay_no, employee_category) VALUES ('1002', 'civil_servant')");

        // พยายามแก้/ลบสิทธิ์ของอัตรา 1 ผ่าน URL ของอัตรา 2 ⇒ ต้องถูกปฏิเสธ
        $dto = new UpdatePositionAllowanceDto(effectiveFrom: '2026-01-01', effectiveTo: null, docNo: null);
        $this->assertFalse($service->update('admin', 2, $id, $dto));
        $this->assertFalse($service->delete('admin', 2, $id));

        // ผ่าน URL ที่ถูกต้องยังทำงานปกติ
        $this->assertTrue($service->update('admin', 1, $id, $dto));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateAllowanceRateDto;
use App\Dtos\UpdateAllowanceRateDto;
use App\Services\AllowanceRateService;

class AllowanceRateServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

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
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE allowance_rates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                allowance_type_id INTEGER NOT NULL,
                level_code TEXT,
                line_code TEXT,
                amount REAL,
                percent REAL,
                derives_from_type_id INTEGER,
                fallback_amount REAL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("CREATE TABLE expense_items (id INTEGER PRIMARY KEY AUTOINCREMENT, name_th TEXT)");

        // 3 types: A(1) เงินประจำตำแหน่ง · B(2) ค.ต.น. · C(3) พ.ต.ก.
        foreach (['POSITION_ALLOWANCE', 'KHN', 'PTK'] as $code) {
            $this->pdo->exec("INSERT INTO allowance_types (code, name_th) VALUES ('{$code}', '{$code}')");
        }
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function rateDto(int $typeId, ?int $derivesFrom, ?float $fallback = null): CreateAllowanceRateDto
    {
        return new CreateAllowanceRateDto(
            allowanceTypeId: $typeId,
            levelCode: 'ชำนาญการพิเศษ',
            lineCode: null,
            amount: null,
            percent: null,
            derivesFromTypeId: $derivesFrom,
            fallbackAmount: $fallback,
            effectiveFrom: '2025-10-01',
            effectiveTo: null,
            docNo: null,
        );
    }

    /** @test */
    public function create_flat_rate_as_admin(): void
    {
        $service = new AllowanceRateService();
        $dto = new CreateAllowanceRateDto(
            allowanceTypeId: 1, levelCode: 'ชำนาญการ', lineCode: null,
            amount: 3500.0, percent: null, derivesFromTypeId: null,
            fallbackAmount: null, effectiveFrom: '2025-10-01',
            effectiveTo: null, docNo: null,
        );

        $this->assertNotNull($service->create('admin', $dto));
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new AllowanceRateService();
        $this->assertNull($service->create('viewer', $this->rateDto(1, null)));
    }

    /** @test */
    public function self_cycle_is_rejected(): void
    {
        $service = new AllowanceRateService();
        // A อ้าง A เอง
        $this->assertNull($service->create('admin', $this->rateDto(1, 1)));
    }

    /** @test */
    public function two_node_cycle_is_rejected(): void
    {
        $service = new AllowanceRateService();

        // B อ้าง A — ปกติ
        $this->assertNotNull($service->create('admin', $this->rateDto(2, 1, 3500.0)));

        // A อ้าง B — กลับเป็นวงจร B→A→B ⇒ ต้องถูกปฏิเสธ
        $this->assertNull($service->create('admin', $this->rateDto(1, 2)));
    }

    /** @test */
    public function long_chain_without_cycle_is_accepted(): void
    {
        $service = new AllowanceRateService();

        $this->assertNotNull($service->create('admin', $this->rateDto(2, 1))); // B→A
        $this->assertNotNull($service->create('admin', $this->rateDto(3, 2))); // C→B→A ไม่วงจร
    }

    /** @test */
    public function update_to_creating_cycle_is_rejected(): void
    {
        $service = new AllowanceRateService();
        $bId = $service->create('admin', $this->rateDto(2, 1)); // B→A
        $cId = $service->create('admin', $this->rateDto(3, 2)); // C→B→A

        $this->assertNotNull($bId);
        $this->assertNotNull($cId);

        // แก้ A ให้อ้าง C ⇒ A→C→B→A วงจร ⇒ ปฏิเสธ
        $dto = new UpdateAllowanceRateDto(
            levelCode: null, lineCode: null, amount: null, percent: null,
            derivesFromTypeId: 3, fallbackAmount: 100.0,
            effectiveFrom: null, effectiveTo: null, docNo: null,
        );
        $this->assertFalse($service->update('admin', $bId, $dto));
    }

    /** @test */
    public function cycle_guard_ignores_the_row_being_edited(): void
    {
        $service = new AllowanceRateService();
        $bId = $service->create('admin', $this->rateDto(2, 1, 3500.0));
        $this->assertNotNull($bId);

        // แก้ fallback ของแถวเดิมโดยยังอ้าง A เหมือนเดิม ต้องไม่ตีความเป็นวงจร
        $dto = new UpdateAllowanceRateDto(
            levelCode: null, lineCode: null, amount: null, percent: null,
            derivesFromTypeId: null, fallbackAmount: 4000.0,
            effectiveFrom: null, effectiveTo: null, docNo: null,
        );
        $this->assertTrue($service->update('admin', $bId, $dto));
    }

    /** @test */
    public function deleting_last_rate_of_derived_parent_type_is_blocked(): void
    {
        $service = new AllowanceRateService();

        // type 1 (เงินประจำตำแหน่ง) มีอัตราปกติ 1 แถว
        $plain = new CreateAllowanceRateDto(
            allowanceTypeId: 1, levelCode: 'ชำนาญการ', lineCode: null,
            amount: 3500.0, percent: null, derivesFromTypeId: null,
            fallbackAmount: null, effectiveFrom: '2025-10-01',
            effectiveTo: null, docNo: null,
        );
        $plainId = $service->create('admin', $plain);

        // type 2 (ค.ต.น.) อ้างอิง type 1
        $derivedId = $service->create('admin', $this->rateDto(2, 1, 3500.0));
        $this->assertNotNull($plainId);
        $this->assertNotNull($derivedId);

        // ลบอัตราเดียวของ type 1 ที่มีลูกอ้างอิง ⇒ ต้องถูกบล็อก
        $this->assertFalse($service->delete('admin', $plainId));

        // เพิ่มอัตราอีกแถวให้ type 1 ⇒ ตอนนี้ลบแถวแรกได้ (ลูกยังมีที่อ้าง)
        $another = new CreateAllowanceRateDto(
            allowanceTypeId: 1, levelCode: 'เชี่ยวชาญ', lineCode: null,
            amount: 5600.0, percent: null, derivesFromTypeId: null,
            fallbackAmount: null, effectiveFrom: '2025-10-01',
            effectiveTo: null, docNo: null,
        );
        $this->assertNotNull($service->create('admin', $another));
        $this->assertTrue($service->delete('admin', $plainId));

        // ลบแถวลูก (type 2) ได้ตามปกติ — ไม่มีใครอ้างอิงมัน
        $this->assertTrue($service->delete('admin', $derivedId));
    }

    /** @test */
    public function derives_from_missing_type_fails(): void
    {
        $service = new AllowanceRateService();
        $this->assertNull($service->create('admin', $this->rateDto(1, 999)));
    }
}

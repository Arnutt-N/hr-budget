<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateAllowanceRateDto;
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
        $this->assertFalse($service->update('admin', $bId, ['derives_from_type_id' => 3, 'fallback_amount' => 100]));
    }

    /** @test */
    public function cycle_guard_ignores_the_row_being_edited(): void
    {
        $service = new AllowanceRateService();
        $bId = $service->create('admin', $this->rateDto(2, 1, 3500.0));
        $this->assertNotNull($bId);

        // แก้ fallback ของแถวเดิมโดยยังอ้าง A เหมือนเดิม ต้องไม่ตีความเป็นวงจร
        $this->assertTrue($service->update('admin', $bId, ['fallback_amount' => 4000.0]));
    }

    /** @test */
    public function derives_from_missing_type_fails(): void
    {
        $service = new AllowanceRateService();
        $this->assertNull($service->create('admin', $this->rateDto(1, 999)));
    }
}

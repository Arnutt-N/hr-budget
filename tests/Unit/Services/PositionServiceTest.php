<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePositionDto;
use App\Dtos\CreatePositionVersionDto;
use App\Dtos\UpdatePositionDto;
use App\Dtos\UpdatePositionVersionDto;
use App\Services\PositionService;

class PositionServiceTest extends TestCase
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
                created_doc_no TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE position_versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                pos_no TEXT,
                level_code TEXT,
                line_code TEXT,
                base_salary REAL DEFAULT 0,
                salary_basis TEXT DEFAULT 'estimated',
                salary_pre_raise REAL,
                occupancy TEXT DEFAULT 'occupied',
                lifecycle TEXT DEFAULT 'active',
                months_counted INTEGER DEFAULT 12,
                approval_status TEXT DEFAULT 'approved',
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                order_doc_no TEXT,
                order_doc_date TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE organizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name_th TEXT NOT NULL
            )
        ");
        $this->pdo->exec("INSERT INTO organizations (name_th) VALUES ('สำนักงานทดสอบ')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeCreateDto(string $payNo = '1001'): CreatePositionDto
    {
        return new CreatePositionDto(
            payNo: $payNo,
            employeeCategory: 'civil_servant',
            createdDocNo: 'กค 0429/2569',
            organizationId: 1,
            posNo: 'กค-001',
            levelCode: 'ชำนาญการพิเศษ',
            lineCode: null,
            baseSalary: 26460.0,
            occupancy: 'occupied',
            monthsCounted: 12,
            effectiveFrom: '2025-10-01',
            orderDocNo: null,
            orderDocDate: null,
        );
    }

    /** @test */
    public function create_position_with_first_version_as_admin(): void
    {
        $service = new PositionService();

        $id = $service->create('admin', $this->makeCreateDto());
        $this->assertNotNull($id);

        $position = $service->findById($id);
        $this->assertSame('1001', $position['pay_no']);
        $this->assertCount(1, $position['versions']);
        $this->assertSame('estimated', $position['versions'][0]['salary_basis']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new PositionService();
        $this->assertNull($service->create('viewer', $this->makeCreateDto()));
    }

    /** @test */
    public function duplicate_pay_no_fails(): void
    {
        $service = new PositionService();
        $this->assertNotNull($service->create('admin', $this->makeCreateDto()));
        $this->assertNull($service->create('admin', $this->makeCreateDto()));
    }

    /** @test */
    public function update_rejects_pay_no_owned_by_another_position(): void
    {
        $service = new PositionService();
        $service->create('admin', $this->makeCreateDto('1001'));
        $id2 = $service->create('admin', $this->makeCreateDto('1002'));

        $ok = $service->update('admin', $id2, new UpdatePositionDto('1001', null, null));
        $this->assertFalse($ok);
    }

    /** @test */
    public function create_version_closes_the_open_previous_version(): void
    {
        $service = new PositionService();
        $id = $service->create('admin', $this->makeCreateDto());

        $dto = new CreatePositionVersionDto(
            organizationId: 1,
            posNo: 'กค-001',
            levelCode: 'เชี่ยวชาญ',
            lineCode: null,
            baseSalary: 29000.0,
            salaryBasis: 'actual',
            salaryPreRaise: 26460.0,
            occupancy: 'occupied',
            lifecycle: 'active',
            monthsCounted: 9,
            approvalStatus: 'approved',
            effectiveFrom: '2026-01-01',
            effectiveTo: null,
            orderDocNo: 'กค 0555/2569',
            orderDocDate: '2025-12-20',
        );

        $versionId = $service->createVersion('admin', $id, $dto);
        $this->assertNotNull($versionId);

        $versions = $service->listVersions($id);
        $this->assertCount(2, $versions);

        $byFrom = [];
        foreach ($versions as $v) {
            $byFrom[$v['effective_from']] = $v;
        }
        // เวอร์ชันเดิมถูกปิดที่วันก่อนเวอร์ชันใหม่เริ่ม
        $this->assertSame('2025-12-31', $byFrom['2025-10-01']['effective_to']);
        // เวอร์ชันใหม่ยังเปิดอยู่
        $this->assertNull($byFrom['2026-01-01']['effective_to']);
    }

    /** @test */
    public function create_version_on_missing_position_fails(): void
    {
        $service = new PositionService();
        $dto = new CreatePositionVersionDto(
            organizationId: 1, posNo: null, levelCode: null, lineCode: null,
            baseSalary: 10000.0, salaryBasis: 'estimated', salaryPreRaise: null,
            occupancy: 'vacant_funded', lifecycle: 'active', monthsCounted: 12,
            approvalStatus: 'requested', effectiveFrom: '2025-10-01',
            effectiveTo: null, orderDocNo: null, orderDocDate: null,
        );

        $this->assertNull($service->createVersion('admin', 999, $dto));
    }

    /** @test */
    public function update_version_rejects_out_of_range_months_counted(): void
    {
        $service = new PositionService();
        $id = $service->create('admin', $this->makeCreateDto());
        $versions = $service->listVersions($id);
        $versionId = (int) $versions[0]['id'];

        $dto = new UpdatePositionVersionDto(
            organizationId: null, posNo: null, levelCode: null, lineCode: null,
            baseSalary: null, salaryBasis: null, salaryPreRaise: null,
            occupancy: null, lifecycle: null, monthsCounted: 13,
            approvalStatus: null, effectiveFrom: null, effectiveTo: null,
            orderDocNo: null, orderDocDate: null,
        );
        $this->assertFalse($service->updateVersion('admin', $id, $versionId, $dto));

        $dtoOk = new UpdatePositionVersionDto(
            organizationId: null, posNo: null, levelCode: null, lineCode: null,
            baseSalary: null, salaryBasis: null, salaryPreRaise: null,
            occupancy: null, lifecycle: null, monthsCounted: 6,
            approvalStatus: null, effectiveFrom: null, effectiveTo: null,
            orderDocNo: null, orderDocDate: null,
        );
        $this->assertTrue($service->updateVersion('admin', $id, $versionId, $dtoOk));
    }

    /** @test */
    public function create_version_in_the_past_cannot_overlap_closed_version(): void
    {
        $service = new PositionService();
        $id = $service->create('admin', $this->makeCreateDto()); // 2025-10-01 → ปัจจุบัน

        // เพิ่มเวอร์ชันที่สองปกติ → เวอร์ชันแรกถูกปิดที่ 2025-12-31
        $dto = new CreatePositionVersionDto(
            organizationId: 1, posNo: null, levelCode: 'เชี่ยวชาญ', lineCode: null,
            baseSalary: 30000.0, salaryBasis: 'actual', salaryPreRaise: null,
            occupancy: 'occupied', lifecycle: 'active', monthsCounted: 12,
            approvalStatus: 'approved', effectiveFrom: '2026-01-01',
            effectiveTo: null, orderDocNo: null, orderDocDate: null,
        );
        $this->assertNotNull($service->createVersion('admin', $id, $dto));

        // พยายามแทรกย้อนหลังช่วง 2025-11-01..2025-11-30 — ตัดกับเวอร์ชันปิดแล้ว (2025-10-01..2025-12-31) ⇒ ปฏิเสธ
        $overlap = new CreatePositionVersionDto(
            organizationId: 1, posNo: null, levelCode: null, lineCode: null,
            baseSalary: 28000.0, salaryBasis: 'estimated', salaryPreRaise: null,
            occupancy: 'occupied', lifecycle: 'active', monthsCounted: 12,
            approvalStatus: 'approved', effectiveFrom: '2025-11-01',
            effectiveTo: '2025-11-30', orderDocNo: null, orderDocDate: null,
        );
        $this->assertNull($service->createVersion('admin', $id, $overlap));

        // จำนวนเวอร์ชันยังเป็น 2 — แถวซ้อนไม่หลุดเข้าไป
        $this->assertCount(2, $service->listVersions($id));
    }

    /** @test */
    public function update_version_cannot_move_range_onto_sibling(): void
    {
        $service = new PositionService();
        $id = $service->create('admin', $this->makeCreateDto());

        $second = new CreatePositionVersionDto(
            organizationId: 1, posNo: null, levelCode: null, lineCode: null,
            baseSalary: 30000.0, salaryBasis: 'actual', salaryPreRaise: null,
            occupancy: 'occupied', lifecycle: 'active', monthsCounted: 12,
            approvalStatus: 'approved', effectiveFrom: '2026-01-01',
            effectiveTo: null, orderDocNo: null, orderDocDate: null,
        );
        $service->createVersion('admin', $id, $second);

        $versions = $service->listVersions($id);
        $secondId = null;
        foreach ($versions as $v) {
            if ($v['effective_from'] === '2026-01-01') {
                $secondId = (int) $v['id'];
            }
        }
        $this->assertNotNull($secondId);

        // ขยับเวอร์ชันที่สองย้อนไปช่วงที่ตัดกับเวอร์ชันแรก ⇒ ปฏิเสธ
        $move = new UpdatePositionVersionDto(
            organizationId: null, posNo: null, levelCode: null, lineCode: null,
            baseSalary: null, salaryBasis: null, salaryPreRaise: null,
            occupancy: null, lifecycle: null, monthsCounted: null,
            approvalStatus: null, effectiveFrom: '2025-12-15', effectiveTo: null,
            orderDocNo: null, orderDocDate: null,
        );
        $this->assertFalse($service->updateVersion('admin', $id, $secondId, $move));
    }

    /** @test */
    public function soft_delete_hides_position(): void
    {
        $service = new PositionService();
        $id = $service->create('admin', $this->makeCreateDto());

        $this->assertTrue($service->delete('admin', $id));
        $this->assertNull($service->findById($id));
    }

    /** @test */
    public function list_filters_by_occupancy_of_current_version(): void
    {
        $service = new PositionService();
        $service->create('admin', $this->makeCreateDto('1001'));

        $dto = $this->makeCreateDto('1002');
        $service->create('admin', new CreatePositionDto(
            payNo: '1002',
            employeeCategory: $dto->employeeCategory,
            createdDocNo: null,
            organizationId: 1,
            posNo: null,
            levelCode: null,
            lineCode: null,
            baseSalary: 15000.0,
            occupancy: 'vacant_funded',
            monthsCounted: 12,
            effectiveFrom: '2025-10-01',
            orderDocNo: null,
            orderDocDate: null,
        ));

        $occupied = $service->list(1, 50, ['occupancy' => 'occupied']);
        $this->assertSame(1, $occupied['meta']['total']);

        $vacant = $service->list(1, 50, ['occupancy' => 'vacant_funded']);
        $this->assertSame(1, $vacant['meta']['total']);
        $this->assertSame('1002', $vacant['data'][0]['pay_no']);
    }
}

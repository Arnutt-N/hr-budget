<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Services\PersonnelBudgetService;

/**
 * Tracer-bullet tests สำหรับตัวคำนวณงบบุคลากร → budget_line_items (source='computed')
 * ตามสูตรใน PRPs/2026-08-09_personnel-budget-schema-design.md §สูตร
 */
class PersonnelBudgetServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("CREATE TABLE organizations (id INTEGER PRIMARY KEY AUTOINCREMENT, name_th TEXT, region TEXT)");
        $this->pdo->exec("CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY AUTOINCREMENT, year INTEGER NOT NULL)");
        $this->pdo->exec("INSERT INTO fiscal_years (year) VALUES (2569)");
        $this->pdo->exec("INSERT INTO organizations (name_th, region) VALUES ('กองบริหารทรัพยากรบุคคล', 'central')");

        $this->pdo->exec("
            CREATE TABLE positions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pay_no TEXT NOT NULL,
                employee_category TEXT NOT NULL,
                created_doc_no TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE position_versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                pos_no TEXT, level_code TEXT, line_code TEXT,
                base_salary REAL DEFAULT 0,
                salary_basis TEXT DEFAULT 'estimated',
                salary_pre_raise REAL,
                occupancy TEXT DEFAULT 'occupied',
                lifecycle TEXT DEFAULT 'active',
                months_counted INTEGER DEFAULT 12,
                approval_status TEXT DEFAULT 'approved',
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE allowance_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL, name_th TEXT NOT NULL, short_name TEXT,
                expense_item_id INTEGER,
                scope TEXT DEFAULT 'position',
                vacant_eligible INTEGER DEFAULT 0,
                report_scope TEXT DEFAULT 'personnel',
                basis TEXT DEFAULT 'flat',
                rate_kind TEXT DEFAULT 'exact',
                budget_basis TEXT DEFAULT 'establishment',
                legal_ref TEXT, is_active INTEGER DEFAULT 1, deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE allowance_rates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                allowance_type_id INTEGER NOT NULL,
                level_code TEXT, line_code TEXT,
                amount REAL, percent REAL,
                derives_from_type_id INTEGER,
                fallback_amount REAL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT, is_active INTEGER DEFAULT 1, deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE position_allowances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                allowance_type_id INTEGER NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT, is_active INTEGER DEFAULT 1, deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE salary_scales (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_category TEXT NOT NULL,
                level_code TEXT NOT NULL,
                min_amount REAL NOT NULL,
                max_amount REAL NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE salary_increment_policies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year_id INTEGER NOT NULL,
                employee_category TEXT NOT NULL,
                max_percent REAL NOT NULL,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE salary_raise_rounds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                round_month TEXT NOT NULL,
                round_year_be INTEGER NOT NULL,
                effective_date TEXT NOT NULL,
                fiscal_year_id INTEGER NOT NULL,
                include_in_budget INTEGER DEFAULT 0,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE salary_raise_progress (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                round_id INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                completed_at TEXT,
                doc_no TEXT, is_active INTEGER DEFAULT 1, deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE personnel_budget_policies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year_id INTEGER NOT NULL,
                vacancy_rule TEXT,
                calc_mode TEXT DEFAULT 'prorate',
                buffer_percent REAL,
                reference_date TEXT,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE vacancy_recruitment (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                fiscal_year_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                doc_no TEXT, doc_date TEXT,
                is_active INTEGER DEFAULT 1, deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE budget_line_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER,
                department_id INTEGER,
                expense_item_id INTEGER,
                source TEXT DEFAULT 'manual',
                allocated_pba REAL DEFAULT 0,
                status TEXT DEFAULT 'active',
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

    /** วางอัตรา 1 ตำแหน่ง + policy ปีงบ 2569 (prorate, ไม่มีรอบเลื่อน) */
    private function seedPosition(array $overrides = []): int
    {
        $this->pdo->exec("INSERT INTO positions (pay_no, employee_category) VALUES ('1001', 'civil_servant')");
        $positionId = (int) $this->pdo->lastInsertId();

        $defaults = [
            'organization_id' => 1,
            'level_code' => 'ชำนาญการ',
            'base_salary' => 10000.0,
            'occupancy' => 'occupied',
            'months_counted' => 12,
            'approval_status' => 'approved',
            'effective_from' => '2025-10-01',
        ];
        $row = array_merge($defaults, $overrides);
        $stmt = $this->pdo->prepare(
            "INSERT INTO position_versions
             (position_id, organization_id, level_code, base_salary, occupancy, months_counted,
              approval_status, effective_from, salary_basis)
             VALUES (:position_id, :organization_id, :level_code, :base_salary, :occupancy,
                     :months_counted, :approval_status, :effective_from, 'actual')"
        );
        $stmt->execute([
            ':position_id' => $positionId,
            ':organization_id' => $row['organization_id'],
            ':level_code' => $row['level_code'],
            ':base_salary' => $row['base_salary'],
            ':occupancy' => $row['occupancy'],
            ':months_counted' => $row['months_counted'],
            ':approval_status' => $row['approval_status'],
            ':effective_from' => $row['effective_from'],
        ]);

        return $positionId;
    }

    private function seedPolicy(): void
    {
        $this->pdo->exec(
            "INSERT INTO personnel_budget_policies (fiscal_year_id, vacancy_rule, calc_mode, reference_date)
             VALUES (1, 'ready_to_fill', 'prorate', '2025-10-01')"
        );
    }

    /** @test */
    public function occupied_position_salary_lands_on_existing_rate_item(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['base_salary' => 10000.0, 'months_counted' => 12]);

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // เงินเดือนอัตราเดิม 12 เดือน = 120,000 ลง ei 2 (อัตราเดิม) ของหน่วยงาน 1
        $this->assertSame(120000.0, $result['lines']['2:1']);
    }

    /** @test */
    public function vacant_funded_passing_vacancy_rule_prorates_onto_new_rate_item(): void
    {
        $this->seedPolicy();
        $vacantId = $this->seedPosition([
            'occupancy' => 'vacant_funded',
            'months_counted' => 9, // บรรจุ ม.ค. → นับ 9 เดือน
            'base_salary' => 10000.0,
        ]);

        // ผ่านเกณฑ์ "พร้อมบรรจุ" ของปีงบนี้
        $this->pdo->exec(
            "INSERT INTO vacancy_recruitment (position_id, fiscal_year_id, type)
             VALUES ($vacantId, 1, 'ready_to_fill')"
        );

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // อัตราใหม่ 9 เดือน = 90,000 ลง ei 3 · ไม่มีเงินก้อนอื่น
        $this->assertSame(90000.0, $result['lines']['3:1']);
        $this->assertArrayNotHasKey('2:1', $result['lines']);
    }

    /** @test */
    public function vacant_funded_without_matching_rule_is_excluded(): void
    {
        $this->seedPolicy();
        $vacantId = $this->seedPosition([
            'occupancy' => 'vacant_funded',
            'months_counted' => 9,
            'base_salary' => 10000.0,
        ]);
        // ไม่มีแถว vacancy_recruitment เลย

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        $this->assertSame([], $result['lines']);
    }

    /** @test */
    public function vacant_unfunded_is_excluded_and_requested_version_is_excluded(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['occupancy' => 'vacant_unfunded', 'months_counted' => 12]);
        $this->seedPosition(['approval_status' => 'requested', 'months_counted' => 12]);

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        $this->assertSame([], $result['lines']);
    }

    /** @test */
    public function pending_org_salary_is_projected_with_policy_percent(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['base_salary' => 10000.0]); // occupied, หน่วย 1 ยังไม่เลื่อน

        // รอบ ต.ค. 2568 นับในงบ + นโยบายประมาณการ 3%
        $this->pdo->exec(
            "INSERT INTO salary_raise_rounds (round_month, round_year_be, effective_date, fiscal_year_id, include_in_budget)
             VALUES ('oct', 2568, '2025-10-01', 1, 1)"
        );
        $this->pdo->exec(
            "INSERT INTO salary_increment_policies (fiscal_year_id, employee_category, max_percent)
             VALUES (1, 'civil_servant', 3.0)"
        );

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // 10,000 × 1.03 × 12 = 123,600
        $this->assertSame(123600.0, $result['lines']['2:1']);
    }

    /** @test */
    public function projection_is_capped_at_scale_maximum(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['base_salary' => 10000.0]);

        $this->pdo->exec(
            "INSERT INTO salary_raise_rounds (round_month, round_year_be, effective_date, fiscal_year_id, include_in_budget)
             VALUES ('oct', 2568, '2025-10-01', 1, 1)"
        );
        $this->pdo->exec(
            "INSERT INTO salary_increment_policies (fiscal_year_id, employee_category, max_percent)
             VALUES (1, 'civil_servant', 3.0)"
        );
        // เพดานขั้นสูง 10,250 — ต่ำกว่า 10,300
        $this->pdo->exec(
            "INSERT INTO salary_scales (employee_category, level_code, min_amount, max_amount, effective_from)
             VALUES ('civil_servant', 'ชำนาญการ', 5000, 10250, '2025-10-01')"
        );

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // ถูกตัดที่เพดาน: 10,250 × 12 = 123,000
        $this->assertSame(123000.0, $result['lines']['2:1']);
    }

    /** @test */
    public function completed_org_salary_is_actual_base(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['base_salary' => 10000.0]);

        $this->pdo->exec(
            "INSERT INTO salary_raise_rounds (round_month, round_year_be, effective_date, fiscal_year_id, include_in_budget)
             VALUES ('oct', 2568, '2025-10-01', 1, 1)"
        );
        $this->pdo->exec(
            "INSERT INTO salary_increment_policies (fiscal_year_id, employee_category, max_percent)
             VALUES (1, 'civil_servant', 3.0)"
        );
        // หน่วยงาน 1 เลื่อนเสร็จแล้ว ⇒ actual — ใช้ฐานเดิม (base ยังเป็นค่าหลังเลื่อนจริง)
        $this->pdo->exec(
            "INSERT INTO salary_raise_progress (round_id, organization_id, status)
             VALUES (1, 1, 'completed')"
        );

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        $this->assertSame(120000.0, $result['lines']['2:1']);
    }

    /** helper: สิทธิ์เงินเพิ่ม + อัตรา flat ให้ตำแหน่ง */
    private function seedAllowance(int $positionId, int $typeId, float $amount, ?int $expenseItemId = 5): void
    {
        $this->pdo->exec(
            "INSERT INTO position_allowances (position_id, allowance_type_id, effective_from)
             VALUES ($positionId, $typeId, '2025-10-01')"
        );
        $this->pdo->exec(
            "INSERT INTO allowance_rates (allowance_type_id, level_code, amount, effective_from)
             VALUES ($typeId, 'ชำนาญการ', $amount, '2025-10-01')"
        );
        // $typeId ใช้ซ้ำเป็น id ของ type — seed เฉพาะครั้งแรก
        $exists = $this->pdo->query("SELECT COUNT(*) FROM allowance_types WHERE id = $typeId")->fetchColumn();
        if (!(int) $exists) {
            $this->pdo->exec(
                "INSERT INTO allowance_types (id, code, name_th, expense_item_id, vacant_eligible, basis)
                 VALUES ($typeId, 'T$typeId', 'ทดสอบ $typeId', $expenseItemId, 0, 'flat')"
            );
        }
    }

    /** @test */
    public function flat_allowance_entitlement_lands_on_bridged_expense_item(): void
    {
        $this->seedPolicy();
        $positionId = $this->seedPosition(['base_salary' => 10000.0]);
        $this->seedAllowance($positionId, typeId: 5, amount: 3500.0, expenseItemId: 5);

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // เงินเดือน 120,000 ลง ei2 · เงินประจำตำแหน่ง 3,500×12 = 42,000 ลง ei5 (สะพานของ type)
        $this->assertSame(120000.0, $result['lines']['2:1']);
        $this->assertSame(42000.0, $result['lines']['5:1']);
    }

    /** @test */
    public function vacant_position_gets_allowance_only_when_vacant_eligible(): void
    {
        $this->seedPolicy();
        // อัตราว่างพร้อมบรรจุ ผ่านเกณฑ์ — เงินเดือน 9 เดือน
        $vacantId = $this->seedPosition(['occupancy' => 'vacant_funded', 'months_counted' => 9, 'base_salary' => 10000.0]);
        $this->pdo->exec(
            "INSERT INTO vacancy_recruitment (position_id, fiscal_year_id, type) VALUES ($vacantId, 1, 'ready_to_fill')"
        );

        // type 5: vacant_eligible=0 (default จาก seedAllowance)
        $this->seedAllowance($vacantId, typeId: 5, amount: 3500.0);
        // type 21: vacant_eligible=1
        $this->pdo->exec(
            "INSERT INTO allowance_types (id, code, name_th, expense_item_id, vacant_eligible, basis)
             VALUES (21, 'SPP', 'สปพ.', 21, 1, 'flat')"
        );
        $this->seedAllowance($vacantId, typeId: 21, amount: 1000.0);

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // ei21 ได้ 1,000×9 = 9,000 · ei5 ไม่ได้ (นโยบายไม่นับอัตราว่าง)
        $this->assertSame(9000.0, $result['lines']['21:1']);
        $this->assertArrayNotHasKey('5:1', $result['lines']);
    }

    /** @test */
    public function derived_allowance_equals_parent_and_falls_back_when_parent_absent(): void
    {
        $this->seedPolicy();
        $positionId = $this->seedPosition(['base_salary' => 10000.0]);

        // แม่: เงินประจำตำแหน่ง type 5 = 5,600 (ei 5)
        $this->seedAllowance($positionId, typeId: 5, amount: 5600.0);
        // ลูก: ค.ต.น. type 12 derived → type 5, fallback 3,500 (ei 12)
        $this->pdo->exec(
            "INSERT INTO allowance_types (id, code, name_th, expense_item_id, basis)
             VALUES (12, 'KHN', 'ค.ต.น.', 12, 'derived')"
        );
        $this->pdo->exec(
            "INSERT INTO position_allowances (position_id, allowance_type_id, effective_from)
             VALUES ($positionId, 12, '2025-10-01')"
        );
        $this->pdo->exec(
            "INSERT INTO allowance_rates (allowance_type_id, derives_from_type_id, fallback_amount, effective_from)
             VALUES (12, 5, 3500, '2025-10-01')"
        );

        // อัตราที่สอง (ชำนาญการ ไม่มีเงินประจำตำแหน่ง = แม่ 0) — derived ต้องได้ fallback 3,500
        $plainId = $this->seedPosition([
            'pay_no' => '1002',
            'base_salary' => 8000.0,
            'level_code' => 'ปฏิบัติการ',
        ]);
        $this->pdo->exec(
            "INSERT INTO position_allowances (position_id, allowance_type_id, effective_from)
             VALUES ($plainId, 12, '2025-10-01')"
        );

        $result = (new PersonnelBudgetService())->compute(1, dryRun: true);

        // ตำแหน่งแรก: ค.ต.น. = 5,600×12 = 67,200 · ตำแหน่งสอง: fallback 3,500×12 = 42,000
        $this->assertSame(67200.0 + 42000.0, $result['lines']['12:1']);
    }

    /** @test */
    public function commit_replaces_only_computed_rows_and_keeps_manual(): void
    {
        $this->seedPolicy();
        $this->seedPosition(['base_salary' => 10000.0]);

        // แถว manual ของมนุษย์ + แถว computed ครั้งก่อน (ตัวเลขเก่า)
        $this->pdo->exec(
            "INSERT INTO budget_line_items (fiscal_year, department_id, expense_item_id, source, allocated_pba)
             VALUES (2569, 1, 2, 'manual', 999.0),
                    (2569, 1, 2, 'computed', 1.0)"
        );

        $service = new PersonnelBudgetService();

        // dry-run: ไม่แตะอะไรเลย
        $dry = $service->compute(1, dryRun: true);
        $this->assertArrayNotHasKey('written', $dry);
        $rows = $this->pdo->query("SELECT COUNT(*) FROM budget_line_items WHERE source = 'computed'")->fetchColumn();
        $this->assertSame(1, (int) $rows); // ยังเป็นแถวเก่า 1.0

        // commit: แทนที่ computed เดิมด้วย 120,000 · manual 999 อยู่ครบ
        $commit = $service->compute(1, dryRun: false);
        $this->assertSame(1, $commit['written']);

        $computed = $this->pdo->query(
            "SELECT allocated_pba FROM budget_line_items WHERE source = 'computed'"
        )->fetchAll(\PDO::FETCH_COLUMN);
        $this->assertCount(1, $computed);
        $this->assertEquals(120000.0, (float) $computed[0]);

        $manual = $this->pdo->query(
            "SELECT COUNT(*) FROM budget_line_items WHERE source = 'manual' AND allocated_pba = 999.0"
        )->fetchColumn();
        $this->assertSame(1, (int) $manual);
    }
}

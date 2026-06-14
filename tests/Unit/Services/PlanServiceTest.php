<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePlanDto;
use App\Dtos\UpdatePlanDto;
use App\Services\PlanService;

class PlanServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                budget_type_id INTEGER,
                code TEXT,
                name_th TEXT,
                name_en TEXT,
                description TEXT,
                fiscal_year INTEGER DEFAULT 2568,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER,
                updated_by INTEGER
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(
        ?string $code = 'PLN-001',
        string $nameTh = 'แผนงานทดสอบ',
        int $fiscalYear = 2569,
        int $sortOrder = 0,
    ): CreatePlanDto {
        return new CreatePlanDto(
            code: $code,
            nameTh: $nameTh,
            nameEn: null,
            description: null,
            fiscalYear: $fiscalYear,
            sortOrder: $sortOrder,
        );
    }

    /** @test */
    public function create_plan_as_admin(): void
    {
        $service = new PlanService();
        $id = $service->create('admin', $this->makeDto());

        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /** @test */
    public function create_plan_as_viewer_fails(): void
    {
        $service = new PlanService();
        $id = $service->create('viewer', $this->makeDto());

        $this->assertNull($id);
    }

    /** @test */
    public function duplicate_code_and_year_returns_null(): void
    {
        $service = new PlanService();
        $first = $service->create('admin', $this->makeDto(code: 'DUP-1', fiscalYear: 2569));
        $this->assertNotNull($first);

        $second = $service->create('admin', $this->makeDto(code: 'DUP-1', fiscalYear: 2569));
        $this->assertNull($second);
    }

    /** @test */
    public function soft_delete_keeps_row_but_hides_from_find(): void
    {
        $service = new PlanService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $ok = $service->delete('admin', $id);
        $this->assertTrue($ok);

        // Soft-deleted: findById (filters deleted_at IS NULL) no longer returns it.
        $this->assertNull($service->findById($id));

        // Physical row still present in the table.
        $raw = $this->pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
        $this->assertEquals(1, (int) $raw);

        // deleted_at column was set.
        $deletedAt = $this->pdo
            ->query("SELECT deleted_at FROM plans WHERE id = {$id}")
            ->fetchColumn();
        $this->assertNotNull($deletedAt);
    }

    /** @test */
    public function delete_as_viewer_fails(): void
    {
        $service = new PlanService();
        $id = $service->create('admin', $this->makeDto());

        $ok = $service->delete('viewer', $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function list_excludes_soft_deleted(): void
    {
        $service = new PlanService();
        $keep = $service->create('admin', $this->makeDto(code: 'KEEP-1'));
        $remove = $service->create('admin', $this->makeDto(code: 'GONE-1'));

        $service->delete('admin', $remove);

        $result = $service->list(1, 10);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals($keep, (int) $result['data'][0]['id']);
    }

    /** @test */
    public function update_changes_field(): void
    {
        $service = new PlanService();
        $id = $service->create('admin', $this->makeDto(nameTh: 'ก่อนแก้ไข'));

        $ok = $service->update('admin', $id, new UpdatePlanDto(nameTh: 'หลังแก้ไข'));
        $this->assertTrue($ok);

        $updated = $service->findById($id);
        $this->assertEquals('หลังแก้ไข', $updated['name_th']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateBudgetTargetDto;
use App\Dtos\UpdateBudgetTargetDto;
use App\Services\BudgetTargetService;

class BudgetTargetServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE budget_targets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                target_type_id INTEGER NOT NULL,
                fiscal_year INTEGER NOT NULL,
                quarter INTEGER,
                organization_id INTEGER,
                category_id INTEGER,
                target_percent REAL,
                target_amount REAL,
                notes TEXT,
                created_by INTEGER,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    /** @test */
    public function create_target_as_admin(): void
    {
        $service = new BudgetTargetService();
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569);

        $id = $service->create('admin', $dto);
        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /** @test */
    public function create_target_as_non_admin_fails(): void
    {
        $service = new BudgetTargetService();
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569);

        $id = $service->create('viewer', $dto);
        $this->assertNull($id);
    }

    /** @test */
    public function delete_non_admin_fails(): void
    {
        $service = new BudgetTargetService();
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569);
        $id = $service->create('admin', $dto);

        $ok = $service->delete('viewer', $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function list_returns_paginated(): void
    {
        $service = new BudgetTargetService();
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569);
        $service->create('admin', $dto);

        $result = $service->list(1, 10);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
    }

    /** @test */
    public function update_target_field_as_admin(): void
    {
        $service = new BudgetTargetService();
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569, targetPercent: 25.0);
        $id = $service->create('admin', $dto);

        $updateDto = new UpdateBudgetTargetDto(targetPercent: 80.0);
        $ok = $service->update('admin', $id, $updateDto);
        $this->assertTrue($ok);

        $item = $service->findById($id);
        $this->assertEquals(80.0, (float) $item['target_percent']);
    }
}

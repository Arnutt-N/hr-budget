<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateFiscalYearDto;
use App\Dtos\UpdateFiscalYearDto;
use App\Services\FiscalYearService;

class FiscalYearServiceTest extends TestCase
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
                year INTEGER NOT NULL,
                start_date TEXT NOT NULL,
                end_date TEXT NOT NULL,
                is_current INTEGER DEFAULT 0,
                is_closed INTEGER DEFAULT 0,
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
    public function create_fiscal_year_as_admin(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');

        $id = $service->create('admin', $dto);
        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /** @test */
    public function create_fiscal_year_as_non_admin_fails(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');

        $id = $service->create('viewer', $dto);
        $this->assertNull($id);
    }

    /** @test */
    public function set_current_resets_previous(): void
    {
        $service = new FiscalYearService();

        $dto1 = new CreateFiscalYearDto(2568, '2024-10-01', '2025-09-30', isCurrent: true);
        $id1 = $service->create('admin', $dto1);
        $this->assertNotNull($id1);

        $dto2 = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30', isCurrent: true);
        $id2 = $service->create('admin', $dto2);
        $this->assertNotNull($id2);

        $fy1 = $service->findById($id1);
        $fy2 = $service->findById($id2);

        $this->assertEquals(0, $fy1['is_current']);
        $this->assertEquals(1, $fy2['is_current']);
    }

    /** @test */
    public function set_current_on_existing_year(): void
    {
        $service = new FiscalYearService();

        $dto1 = new CreateFiscalYearDto(2568, '2024-10-01', '2025-09-30', isCurrent: true);
        $id1 = $service->create('admin', $dto1);

        $dto2 = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $id2 = $service->create('admin', $dto2);

        $ok = $service->setCurrent('admin', $id2);
        $this->assertTrue($ok);

        $fy1 = $service->findById($id1);
        $fy2 = $service->findById($id2);

        $this->assertEquals(0, $fy1['is_current']);
        $this->assertEquals(1, $fy2['is_current']);
    }

    /** @test */
    public function set_current_non_admin_fails(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $id = $service->create('admin', $dto);

        $ok = $service->setCurrent('viewer', $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function delete_non_admin_fails(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $id = $service->create('admin', $dto);

        $ok = $service->delete('viewer', $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function list_returns_paginated(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $service->create('admin', $dto);

        $result = $service->list(1, 10);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
    }

    /** @test */
    public function duplicate_year_returns_null(): void
    {
        $service = new FiscalYearService();
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $service->create('admin', $dto);

        $id2 = $service->create('admin', $dto);
        $this->assertNull($id2);
    }
}

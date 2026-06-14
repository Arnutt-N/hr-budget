<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateDivisionDto;
use App\Dtos\UpdateDivisionDto;
use App\Services\DivisionService;

class DivisionServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE divisions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT,
                name_th TEXT,
                name_en TEXT,
                short_name TEXT,
                parent_id INTEGER,
                type TEXT DEFAULT 'central',
                is_active INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
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
    public function create_division_as_admin(): void
    {
        $service = new DivisionService();
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์');

        $id = $service->create('admin', $dto);

        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /** @test */
    public function create_division_as_non_admin_fails(): void
    {
        $service = new DivisionService();
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์');

        $id = $service->create('viewer', $dto);

        $this->assertNull($id);
    }

    /** @test */
    public function duplicate_code_returns_null(): void
    {
        $service = new DivisionService();
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์');
        $service->create('admin', $dto);

        $id2 = $service->create('admin', $dto);

        $this->assertNull($id2);
    }

    /** @test */
    public function delete_non_admin_fails(): void
    {
        $service = new DivisionService();
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์');
        $id = $service->create('admin', $dto);

        $ok = $service->delete('viewer', $id);

        $this->assertFalse($ok);
    }

    /** @test */
    public function list_returns_paginated(): void
    {
        $service = new DivisionService();
        $service->create('admin', new CreateDivisionDto(code: 'DIV01', nameTh: 'กองหนึ่ง'));
        $service->create('admin', new CreateDivisionDto(code: 'DIV02', nameTh: 'กองสอง'));

        $result = $service->list(1, 10);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['meta']['total']);
    }

    /** @test */
    public function update_changes_a_field(): void
    {
        $service = new DivisionService();
        $id = $service->create('admin', new CreateDivisionDto(code: 'DIV01', nameTh: 'กองเดิม'));

        $ok = $service->update('admin', $id, new UpdateDivisionDto(nameTh: 'กองใหม่'));
        $this->assertTrue($ok);

        $division = $service->findById($id);
        $this->assertEquals('กองใหม่', $division['name_th']);
    }
}

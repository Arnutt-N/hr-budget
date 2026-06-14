<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateTargetTypeDto;
use App\Dtos\UpdateTargetTypeDto;
use App\Services\TargetTypeService;

class TargetTypeServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE target_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT,
                name_th TEXT,
                description TEXT,
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
    public function create_target_type_as_admin(): void
    {
        $service = new TargetTypeService();
        $dto = new CreateTargetTypeDto('TT01', 'บุคลากร');

        $id = $service->create('admin', $dto);
        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /** @test */
    public function create_target_type_as_non_admin_fails(): void
    {
        $service = new TargetTypeService();
        $dto = new CreateTargetTypeDto('TT01', 'บุคลากร');

        $id = $service->create('viewer', $dto);
        $this->assertNull($id);
    }

    /** @test */
    public function duplicate_code_returns_null(): void
    {
        $service = new TargetTypeService();
        $dto = new CreateTargetTypeDto('TT01', 'บุคลากร');
        $service->create('admin', $dto);

        $id2 = $service->create('admin', $dto);
        $this->assertNull($id2);
    }

    /** @test */
    public function delete_non_admin_fails(): void
    {
        $service = new TargetTypeService();
        $dto = new CreateTargetTypeDto('TT01', 'บุคลากร');
        $id = $service->create('admin', $dto);

        $ok = $service->delete('viewer', $id);
        $this->assertFalse($ok);
    }

    /** @test */
    public function list_returns_paginated(): void
    {
        $service = new TargetTypeService();
        $service->create('admin', new CreateTargetTypeDto('TT01', 'บุคลากร'));

        $result = $service->list(1, 10);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
    }

    /** @test */
    public function update_field_as_admin(): void
    {
        $service = new TargetTypeService();
        $id = $service->create('admin', new CreateTargetTypeDto('TT01', 'บุคลากร'));

        $ok = $service->update('admin', $id, new UpdateTargetTypeDto(nameTh: 'หน่วยงาน'));
        $this->assertTrue($ok);

        $item = $service->findById($id);
        $this->assertEquals('หน่วยงาน', $item['name_th']);
    }
}

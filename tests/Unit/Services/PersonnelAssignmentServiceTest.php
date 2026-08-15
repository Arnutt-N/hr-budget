<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePersonnelAssignmentDto;
use App\Services\PersonnelAssignmentService;

class PersonnelAssignmentServiceTest extends TestCase
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
        $this->pdo->exec("CREATE TABLE organizations (id INTEGER PRIMARY KEY AUTOINCREMENT, name_th TEXT NOT NULL)");
        $this->pdo->exec("INSERT INTO organizations (name_th) VALUES ('กอง ก'), ('กอง ข')");
        $this->pdo->exec("
            CREATE TABLE personnel_assignments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                person_id TEXT NOT NULL,
                position_id INTEGER NOT NULL,
                serving_organization_id INTEGER NOT NULL,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                doc_no TEXT,
                doc_date TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("INSERT INTO positions (pay_no, employee_category) VALUES ('1001', 'civil_servant')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(): CreatePersonnelAssignmentDto
    {
        return new CreatePersonnelAssignmentDto(
            personId: 'P-1001',
            positionId: 1,
            servingOrganizationId: 2,
            effectiveFrom: '2025-11-01',
            effectiveTo: null,
            docNo: 'กค 0202/2569',
            docDate: '2025-10-20',
        );
    }

    /** @test */
    public function create_assignment_as_admin(): void
    {
        $service = new PersonnelAssignmentService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $result = $service->list(1, 50, []);
        $this->assertSame(1, $result['meta']['total']);
        $this->assertSame('กอง ข', $result['data'][0]['serving_organization_name']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new PersonnelAssignmentService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function create_with_missing_position_or_org_fails(): void
    {
        $service = new PersonnelAssignmentService();
        $this->assertNull($service->create('admin', new CreatePersonnelAssignmentDto(
            personId: 'X', positionId: 999, servingOrganizationId: 2,
            effectiveFrom: '2025-11-01', effectiveTo: null, docNo: null, docDate: null,
        )));
        $this->assertNull($service->create('admin', new CreatePersonnelAssignmentDto(
            personId: 'X', positionId: 1, servingOrganizationId: 999,
            effectiveFrom: '2025-11-01', effectiveTo: null, docNo: null, docDate: null,
        )));
    }

    /** @test */
    public function list_filters_by_serving_organization(): void
    {
        $service = new PersonnelAssignmentService();
        $service->create('admin', $this->makeDto());

        $result = $service->list(1, 50, ['serving_organization_id' => 2]);
        $this->assertSame(1, $result['meta']['total']);

        $none = $service->list(1, 50, ['serving_organization_id' => 1]);
        $this->assertSame(0, $none['meta']['total']);
    }

    /** @test */
    public function soft_delete_removes_assignment(): void
    {
        $service = new PersonnelAssignmentService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->delete('admin', $id));
        $this->assertSame(0, $service->list(1, 50, [])['meta']['total']);
    }
}

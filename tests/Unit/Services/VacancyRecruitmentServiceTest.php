<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateVacancyRecruitmentDto;
use App\Services\VacancyRecruitmentService;

class VacancyRecruitmentServiceTest extends TestCase
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
        $this->pdo->exec("CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY AUTOINCREMENT, year INTEGER NOT NULL)");
        $this->pdo->exec("INSERT INTO fiscal_years (year) VALUES (2569)");
        $this->pdo->exec("CREATE TABLE organizations (id INTEGER PRIMARY KEY AUTOINCREMENT, name_th TEXT NOT NULL)");
        $this->pdo->exec("INSERT INTO organizations (name_th) VALUES ('กอง ก')");
        $this->pdo->exec("
            CREATE TABLE position_versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                pos_no TEXT,
                effective_from TEXT NOT NULL,
                effective_to TEXT,
                deleted_at TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE vacancy_recruitment (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                position_id INTEGER NOT NULL,
                fiscal_year_id INTEGER NOT NULL,
                type TEXT NOT NULL,
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

    private function makeDto(): CreateVacancyRecruitmentDto
    {
        return new CreateVacancyRecruitmentDto(
            positionId: 1,
            fiscalYearId: 1,
            type: 'ready_to_fill',
            docNo: 'กค 0101/2569',
            docDate: '2025-09-15',
        );
    }

    /** @test */
    public function create_evidence_as_admin(): void
    {
        $service = new VacancyRecruitmentService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $result = $service->list(1, 50, []);
        $this->assertSame(1, $result['meta']['total']);
        $this->assertSame('1001', $result['data'][0]['pay_no']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new VacancyRecruitmentService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function create_with_missing_position_or_fy_fails(): void
    {
        $service = new VacancyRecruitmentService();
        $this->assertNull($service->create('admin', new CreateVacancyRecruitmentDto(
            positionId: 999, fiscalYearId: 1, type: 'ready_to_fill', docNo: null, docDate: null,
        )));
        $this->assertNull($service->create('admin', new CreateVacancyRecruitmentDto(
            positionId: 1, fiscalYearId: 999, type: 'ready_to_fill', docNo: null, docDate: null,
        )));
    }

    /** @test */
    public function list_filters_by_type_and_fiscal_year(): void
    {
        $service = new VacancyRecruitmentService();
        $service->create('admin', $this->makeDto());
        $service->create('admin', new CreateVacancyRecruitmentDto(
            positionId: 1, fiscalYearId: 1, type: 'transfer_request', docNo: null, docDate: null,
        ));

        $ready = $service->list(1, 50, ['type' => 'ready_to_fill']);
        $this->assertSame(1, $ready['meta']['total']);

        $all = $service->list(1, 50, []);
        $this->assertSame(2, $all['meta']['total']);
    }

    /** @test */
    public function soft_delete_removes_evidence(): void
    {
        $service = new VacancyRecruitmentService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $this->assertTrue($service->delete('admin', $id));
        $this->assertSame(0, $service->list(1, 50, [])['meta']['total']);
    }
}

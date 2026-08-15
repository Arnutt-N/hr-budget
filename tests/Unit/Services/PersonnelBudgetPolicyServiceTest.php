<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreatePersonnelBudgetPolicyDto;
use App\Dtos\UpdatePersonnelBudgetPolicyDto;
use App\Services\PersonnelBudgetPolicyService;

class PersonnelBudgetPolicyServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY AUTOINCREMENT, year INTEGER NOT NULL)");
        $this->pdo->exec("INSERT INTO fiscal_years (year) VALUES (2569), (2570)");
        $this->pdo->exec("
            CREATE TABLE personnel_budget_policies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year_id INTEGER NOT NULL,
                vacancy_rule TEXT,
                calc_mode TEXT DEFAULT 'prorate',
                buffer_percent REAL,
                reference_date TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            )
        ");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeDto(int $fyId = 1): CreatePersonnelBudgetPolicyDto
    {
        return new CreatePersonnelBudgetPolicyDto(
            fiscalYearId: $fyId,
            vacancyRule: 'ready_to_fill',
            calcMode: 'prorate',
            bufferPercent: null,
            referenceDate: '2025-10-01',
        );
    }

    /** @test */
    public function create_policy_as_admin(): void
    {
        $service = new PersonnelBudgetPolicyService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $row = $service->findById($id);
        $this->assertSame('ready_to_fill', $row['vacancy_rule']);
        $this->assertSame(2569, $row['fiscal_year']);
    }

    /** @test */
    public function create_as_non_admin_fails(): void
    {
        $service = new PersonnelBudgetPolicyService();
        $this->assertNull($service->create('viewer', $this->makeDto()));
    }

    /** @test */
    public function duplicate_fiscal_year_is_rejected(): void
    {
        $service = new PersonnelBudgetPolicyService();
        $this->assertNotNull($service->create('admin', $this->makeDto()));
        $this->assertNull($service->create('admin', $this->makeDto()));
    }

    /** @test */
    public function create_with_missing_fy_fails(): void
    {
        $service = new PersonnelBudgetPolicyService();
        $this->assertNull($service->create('admin', $this->makeDto(fyId: 999)));
    }

    /** @test */
    public function update_changes_vacancy_rule(): void
    {
        $service = new PersonnelBudgetPolicyService();
        $id = $service->create('admin', $this->makeDto());
        $this->assertNotNull($id);

        $dto = new UpdatePersonnelBudgetPolicyDto(
            vacancyRule: 'eligibility_list',
            calcMode: null,
            bufferPercent: null,
            referenceDate: null,
        );
        $this->assertTrue($service->update('admin', $id, $dto));
        $this->assertSame('eligibility_list', $service->findById($id)['vacancy_rule']);
    }
}

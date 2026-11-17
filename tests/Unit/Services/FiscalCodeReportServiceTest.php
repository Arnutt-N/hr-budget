<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Services\FiscalCodeReportService;
use PDO;
use PHPUnit\Framework\TestCase;

final class FiscalCodeReportServiceTest extends TestCase
{
    private PDO $pdo;
    private FiscalCodeReportService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);
        $this->service = new FiscalCodeReportService();

        $this->pdo->exec(
            "CREATE TABLE expense_types (id INTEGER PRIMARY KEY, fiscal_code TEXT, name_th TEXT);
             CREATE TABLE budget_categories (
                id INTEGER PRIMARY KEY, fiscal_code TEXT, fiscal_code_parent TEXT,
                expense_cat_code TEXT, activity_code TEXT, name_th TEXT
             );
             CREATE TABLE plans (id INTEGER PRIMARY KEY, fiscal_year INTEGER,
                activity_code TEXT, name_th TEXT, deleted_at TEXT);
             CREATE TABLE divisions (id INTEGER PRIMARY KEY, code TEXT, name_th TEXT);
             CREATE TABLE budget_line_items (id INTEGER PRIMARY KEY, fiscal_year INTEGER,
                expense_type_id INTEGER, allocated_pba REAL, deleted_at TEXT);"
        );

        $this->pdo->exec(
            "INSERT INTO expense_types (id, fiscal_code, name_th) VALUES
                (1, '010', 'งบบุคลากร'), (2, '020', 'งบดำเนินงาน');
             INSERT INTO budget_categories (id, fiscal_code, fiscal_code_parent, expense_cat_code, name_th) VALUES
                (1, '16002070012001000001', '1600207001200', '010', 'รายการบุคลากร'),
                (2, '16002070012000200001', '1600207001200', '020', 'รายการดำเนินงาน'),
                (3, '16002140002001000001', '1600214000200', '010', 'รายการบุคลากร 2'),
                (4, '1600207001200', NULL, NULL, 'โครงการแม่'),
                (5, '1600214000200', NULL, NULL, 'โครงการแม่ 2');
             INSERT INTO plans (id, fiscal_year, activity_code, name_th, deleted_at) VALUES
                (1, 2569, '16002690001800000', 'การพัฒนากฎหมาย', NULL),
                (2, 2569, '16002690001800000', 'การพัฒนากฎหมาย', NULL),
                (3, 2569, '16002690002300000', 'การพัฒนาระบบงาน', NULL),
                (4, 2570, '16002690001800000', 'การพัฒนากฎหมาย', NULL),
                (5, 2569, '16002690003200000', 'Activity ลบแล้ว', '2569-01-01');
             INSERT INTO divisions (id, code, name_th) VALUES
                (1, '16002', 'กรมยุติธรรม');
             INSERT INTO budget_line_items (id, fiscal_year, expense_type_id, allocated_pba, deleted_at) VALUES
                (1, 2569, 1, 100, NULL),
                (2, 2569, 1, 50, NULL),
                (3, 2569, 2, 25, NULL),
                (4, 2570, 1, 999, NULL),
                (5, 2569, 1, 999, '2569-01-01');"
        );
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
        parent::tearDown();
    }

    public function test_by_expense_category_reads_thai_labels_and_year_amounts(): void
    {
        $rows = $this->service->byExpenseCategory(2569);

        $this->assertCount(2, $rows);
        $this->assertSame('010', $rows[0]['code']);
        $this->assertSame('งบบุคลากร', $rows[0]['name_th']);
        $this->assertSame(2, (int) $rows[0]['count']);
        $this->assertEquals(150, $rows[0]['allocated_pba']);
        $this->assertSame(1, (int) $rows[1]['count']);
        $this->assertEquals(25, $rows[1]['allocated_pba']);
    }

    public function test_by_activity_filters_fiscal_year(): void
    {
        $rows = $this->service->byActivity(2569);

        $this->assertCount(2, $rows);
        $this->assertSame('16002690001800000', $rows[0]['activity_code']);
        $this->assertSame('การพัฒนากฎหมาย', $rows[0]['name_th']);
        $this->assertSame(2, (int) $rows[0]['count']);
        $this->assertSame('16002690002300000', $rows[1]['activity_code']);
    }

    public function test_by_parent_rollup_counts_children_only(): void
    {
        $rows = $this->service->byParentRollup();

        $this->assertCount(2, $rows);
        $this->assertSame('1600207001200', $rows[0]['parent_code']);
        $this->assertSame('โครงการแม่', $rows[0]['name_th']);
        $this->assertSame(2, (int) $rows[0]['count']);
        $this->assertSame('1600214000200', $rows[1]['parent_code']);
        $this->assertSame(1, (int) $rows[1]['count']);
    }

    public function test_by_agency_groups_leading_five_digits_and_joins_division(): void
    {
        $rows = $this->service->byAgency();

        $this->assertCount(1, $rows);
        $this->assertSame('16002', $rows[0]['agency_code']);
        $this->assertSame('กรมยุติธรรม', $rows[0]['name_th']);
        $this->assertSame(3, (int) $rows[0]['count']);
    }
}

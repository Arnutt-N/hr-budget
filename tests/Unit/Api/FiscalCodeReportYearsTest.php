<?php

declare(strict_types=1);

namespace Tests\Unit\Api;

use App\Api\Controllers\FiscalCodeReportController;
use App\Api\Responses\ApiResponse;
use App\Core\Database;
use App\Core\Jwt;
use PDO;
use PHPUnit\Framework\TestCase;

final class FiscalCodeReportYearsTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);
        $this->pdo->exec('CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY, year INTEGER, is_current INTEGER, is_closed INTEGER)');
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT, is_active INTEGER, email TEXT, name_th TEXT)');
        $this->pdo->exec("INSERT INTO users (id, role, is_active, email, name_th) VALUES (3, 'viewer', 1, 'viewer@test.local', 'ผู้ทดสอบ')");
        $this->pdo->exec('INSERT INTO fiscal_years (id, year, is_current, is_closed) VALUES (1, 2568, 0, 0), (2, 2569, 1, 0)');
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . Jwt::issue(3);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
        ApiResponse::$lastBody = null;
        ApiResponse::$lastStatus = null;
        Database::resetInstance();
        parent::tearDown();
    }

    public function test_report_years_are_readable_without_admin_role(): void
    {
        ob_start();
        (new FiscalCodeReportController())->years(false);
        ob_end_clean();

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertSame([
            'success' => true,
            'data' => [
                ['year' => 2569, 'is_current' => true],
                ['year' => 2568, 'is_current' => false],
            ],
        ], ApiResponse::$lastBody);
    }

    public function test_report_years_fall_back_to_config_when_table_is_empty(): void
    {
        $this->pdo->exec('DELETE FROM fiscal_years');

        ob_start();
        (new FiscalCodeReportController())->years(false);
        ob_end_clean();

        $config = require __DIR__ . '/../../../config/app.php';
        $this->assertSame([
            'success' => true,
            'data' => [[
                'year' => (int) $config['fiscal_year']['current'],
                'is_current' => true,
            ]],
        ], ApiResponse::$lastBody);
    }
}

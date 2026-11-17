<?php

declare(strict_types=1);

namespace Tests\Unit\Api;

use App\Api\Controllers\FiscalCodeReportController;
use App\Core\Database;
use PDO;
use PHPUnit\Framework\TestCase;

final class FiscalCodeReportControllerTest extends TestCase
{
    private PDO $pdo;
    private FiscalCodeReportController $controller;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);
        $this->controller = new FiscalCodeReportController();
        $this->pdo->exec('CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY, year INTEGER, is_current INTEGER)');
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        Database::resetInstance();
        parent::tearDown();
    }

    public function test_default_fiscal_year_comes_from_current_db_year(): void
    {
        $this->pdo->exec('INSERT INTO fiscal_years (id, year, is_current) VALUES (1, 2568, 0), (2, 2569, 1), (3, 2570, 0)');

        $this->assertSame(2569, $this->resolveFiscalYear());
    }

    public function test_explicit_year_overrides_current_db_year(): void
    {
        $this->pdo->exec('INSERT INTO fiscal_years (id, year, is_current) VALUES (1, 2569, 1)');
        $_GET = ['year' => '2570'];

        $this->assertSame(2570, $this->resolveFiscalYear());
    }

    public function test_falls_back_to_config_when_no_current_db_year(): void
    {
        $this->pdo->exec('INSERT INTO fiscal_years (id, year, is_current) VALUES (1, 2568, 0)');

        $config = require __DIR__ . '/../../../config/app.php';
        $this->assertSame((int) $config['fiscal_year']['current'], $this->resolveFiscalYear());
    }

    private function resolveFiscalYear(): int
    {
        $method = new \ReflectionMethod($this->controller, 'resolveFiscalYear');

        return (int) $method->invoke($this->controller);
    }
}

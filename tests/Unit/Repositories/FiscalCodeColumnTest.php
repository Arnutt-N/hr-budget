<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * Schema contract for the fiscal-code columns added by migration 095 (PR3).
 *
 * This reads the real test database, not a fixture, so a mis-typed column or a
 * missing index fails here. PR4's importer writes these columns and expects
 * their exact widths and the unique key. The suite bootstrap refuses to run
 * unless the configured DB name ends in _test.
 */
final class FiscalCodeColumnTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $dbName = getenv('DB_NAME') ?: 'hr_budget_test';
        if (!str_ends_with($dbName, '_test')) {
            $this->markTestSkipped("Refusing schema check on non-test database '{$dbName}'");
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        try {
            $this->pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            $this->markTestSkipped('Test database unavailable: ' . $e->getMessage());
        }
    }

    /** @return array<string, array{type: string, null: string}> */
    private function columns(string $table): array
    {
        $out = [];
        foreach ($this->pdo->query("SHOW COLUMNS FROM `{$table}`") as $row) {
            $out[$row['Field']] = ['type' => strtolower($row['Type']), 'null' => strtoupper($row['Null'])];
        }
        return $out;
    }

    /** @return list<string> */
    private function indexes(string $table): array
    {
        $out = [];
        foreach ($this->pdo->query("SHOW INDEX FROM `{$table}`") as $row) {
            $out[] = $row['Key_name'];
        }
        return $out;
    }

    /** @param array<string, array{type: string, null: string}> $cols */
    private function assertColumn(array $cols, string $name, string $type): void
    {
        $this->assertArrayHasKey($name, $cols, "Column {$name} missing; has migration 095 been applied to the test DB?");
        $this->assertSame($type, $cols[$name]['type'], "Column {$name} has wrong type");
        $this->assertSame('YES', $cols[$name]['null'], "Column {$name} must be nullable");
    }

    public function testBudgetCategoriesColumnsHaveExactWidthsAndAreNullable(): void
    {
        $cols = $this->columns('budget_categories');

        $this->assertColumn($cols, 'fiscal_code', 'char(20)');
        $this->assertColumn($cols, 'fiscal_code_parent', 'char(13)');
        $this->assertColumn($cols, 'activity_code', 'char(17)');
        $this->assertColumn($cols, 'expense_cat_code', 'char(3)');
    }

    public function testPlansAndProjectsColumnsHaveExactWidthsAndAreNullable(): void
    {
        $this->assertColumn($this->columns('plans'), 'activity_code', 'char(17)');
        $this->assertColumn($this->columns('projects'), 'fiscal_code', 'char(20)');
    }

    public function testRequiredIndexesExist(): void
    {
        $bc = $this->indexes('budget_categories');
        foreach (['uk_fiscal_code', 'idx_fiscal_parent', 'idx_activity_code', 'idx_expense_cat'] as $key) {
            $this->assertContains($key, $bc, "budget_categories.{$key} missing");
        }
        $this->assertContains('idx_activity_code', $this->indexes('plans'), 'plans.idx_activity_code missing');
        $this->assertContains('uk_fiscal_code', $this->indexes('projects'), 'projects.uk_fiscal_code missing');
    }

    private function requireFiscalCodeColumn(): void
    {
        $cols = $this->columns('budget_categories');
        if (!isset($cols['fiscal_code'])) {
            $this->markTestSkipped('Migration 095 not applied to test DB');
        }
    }

    /** @return array{code: string, name: string} */
    private function insertCategory(?string $fiscalCode): array
    {
        $suffix = substr(md5(uniqid('', true)), 0, 10);
        $code = 'FC-' . $suffix;
        $stmt = $this->pdo->prepare(
            'INSERT INTO budget_categories (code, name_th, level, fiscal_code) VALUES (?, ?, 1, ?)'
        );
        $stmt->execute([$code, 'fiscal probe ' . $suffix, $fiscalCode]);
        return ['code' => $code, 'name' => $suffix];
    }

    public function testFiscalCodeUniqueKeyRejectsDuplicate(): void
    {
        $this->requireFiscalCodeColumn();
        $code = '16002142002002000000';
        $this->pdo->beginTransaction();
        try {
            $this->insertCategory($code);
            try {
                $this->insertCategory($code);
                $this->fail('Duplicate fiscal_code insert should have been rejected');
            } catch (PDOException $e) {
                $this->assertSame('23000', $e->getCode(), 'Expected a duplicate-key SQLSTATE 23000, got: ' . $e->getMessage());
            }
        } finally {
            $this->pdo->rollBack();
        }
    }

    public function testNullFiscalCodeIsAllowedAlongsidePopulatedRow(): void
    {
        $this->requireFiscalCodeColumn();
        $this->pdo->beginTransaction();
        try {
            $this->insertCategory('16002142002002000000');
            $first = $this->insertCategory(null);
            $second = $this->insertCategory(null);
            $this->assertNotSame($first['code'], $second['code']);
        } finally {
            $this->pdo->rollBack();
        }
    }
}

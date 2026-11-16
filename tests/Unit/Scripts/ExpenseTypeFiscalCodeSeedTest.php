<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/scripts/lib/fiscal_seed_guard.php';

/**
 * Guards the fiscal-code import convention (PR1).
 *
 * These assertions are pure — no DB. They pin two invariants the migration
 * and seed script rely on:
 *   1. The ET- code convention: ET- + substr(md5(name_th), 0, 4).
 *   2. The 6 Thai expense-category codes are exactly 010/020/031/032/041/050,
 *      with no duplicates, so no two categories claim one code.
 */
final class ExpenseTypeFiscalCodeSeedTest extends TestCase
{
    /** Codes the 5 existing expense_types rows carry, keyed by clean name. */
    private const ET_CODE_BY_NAME = [
        'งบบุคลากร'    => 'ET-dda6',
        'งบดำเนินงาน'  => 'ET-57d1',
        'งบลงทุน'      => 'ET-914f',
        'งบรายจ่ายอื่น' => 'ET-dc9b',
        'งบเงินอุดหนุน' => 'ET-8d6b',
    ];

    /** The 6 budget expense-category codes (sheet 16002) and their Thai names. */
    private const CATEGORIES = [
        '010' => 'งบบุคลากร',
        '020' => 'งบดำเนินงาน',
        '031' => 'ครุภัณฑ์และค่าจ้างบริการ',
        '032' => 'ค่าก่อสร้างและปรับปรุงอาคาร',
        '041' => 'งบเงินอุดหนุน',
        '050' => 'ค่าใช้จ่ายโครงการ',
    ];

    public function testEtCodeConventionForExistingFiveNames(): void
    {
        foreach (self::ET_CODE_BY_NAME as $name => $expected) {
            $actual = 'ET-' . substr(md5($name), 0, 4);
            $this->assertSame($expected, $actual, "ET- code mismatch for {$name}");
        }
    }

    public function testEtCodeConventionForThreeNewCategoryNames(): void
    {
        $expected = [
            '031' => 'ET-552e',
            '032' => 'ET-12e2',
            '050' => 'ET-8035',
        ];

        foreach ($expected as $fiscalCode => $etCode) {
            $actual = 'ET-' . substr(md5(self::CATEGORIES[$fiscalCode]), 0, 4);
            $this->assertSame($etCode, $actual, "ET- code mismatch for fiscal code {$fiscalCode}");
        }
    }

    public function testSixCategoryCodesAreExactAndUnique(): void
    {
        $codes = array_keys(self::CATEGORIES);

        $this->assertSame(['010', '020', '031', '032', '041', '050'], $codes);
        $this->assertCount(6, $codes);
        $this->assertCount(count($codes), array_unique($codes), 'Duplicate fiscal code exists');
    }

    public function testGuardRejectsProductionDatabase(): void
    {
        $this->assertNotNull(fiscalSeedDatabaseError('hr_budget'));
    }

    public function testGuardAllowsTestDatabase(): void
    {
        $this->assertNull(fiscalSeedDatabaseError('hr_budget_test'));
    }

    public function testGuardRejectsBareTestSuffix(): void
    {
        $this->assertNotNull(fiscalSeedDatabaseError('_test'));
    }
}

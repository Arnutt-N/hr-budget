<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/scripts/lib/fiscal_import_parsers.php';

final class FiscalImportParsersTest extends TestCase
{
    private const PARENT_CODE = '1600200000001';
    private const CHILD_CODE = '16002000000010100001';

    public function test_budget_duplicate_row_does_not_overwrite_first_name(): void
    {
        $rows = [
            ['header'],
            [null, null, self::PARENT_CODE, 'งบบุคลากร'],
            [null, null, self::CHILD_CODE, 'ชื่อแรก'],
            [null, null, self::CHILD_CODE, 'ชื่อซ้ำ'],
        ];

        $result = parseBudgetRows($rows, 'budget.xlsx', '16002');

        $this->assertSame('ชื่อแรก', $result['children'][self::CHILD_CODE]);
        $this->assertSame('SOURCE_DUPLICATE', $result['report_rows'][2]['reason_code']);
    }
}

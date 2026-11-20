<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/scripts/lib/fiscal_import_preflight.php';

final class FiscalImportPreflightTest extends TestCase
{
    private const BUDGET_FILE = '/source/16002_รหัสงบประมาณ.xlsx';
    private const BUDGET_SHEET = '16002';
    private const ACTIVITY_FILE = '/source/16002_กิจกรรมหลัก.xls';
    private const ACTIVITY_SHEET = 'report';
    private const PARENT_CODE = '1600200000001';
    private const CHILD_CODE = '16002000000010100001';
    private const ORPHAN_CODE = '16002000000020200001';

    public function testBuildsPreflightReportFromParsedRowsWithoutSpreadsheetsOrDatabase(): void
    {
        $rows = [
            self::budgetRow(2, self::PARENT_CODE, 'งบบุคลากร', 'parent', null),
            self::budgetRow(3, self::CHILD_CODE, 'รายการใหม่', 'child', null),
            self::budgetRow(4, self::CHILD_CODE, 'รายการซ้ำ', 'unknown', 'SOURCE_DUPLICATE'),
            self::budgetRow(5, self::ORPHAN_CODE, 'ลูกไม่มีแม่', 'child', 'ORPHAN_CHILD'),
            self::budgetRow(6, 'ABC', 'รหัสไม่ถูกต้อง', 'unknown', 'INVALID_CODE'),
            self::budgetRow(7, '1600200000002', '', 'unknown', 'MISSING_NAME'),
        ];

        $report = buildFiscalImportPreflightReport(
            $rows,
            [self::PARENT_CODE => 1],
            [],
            self::config()
        );

        $this->assertSame([
            'fiscal_year' => 2569,
            'budget_file' => self::BUDGET_FILE,
            'budget_sheet' => self::BUDGET_SHEET,
            'activity_file' => self::ACTIVITY_FILE,
            'activity_sheet' => self::ACTIVITY_SHEET,
        ], $report['source']);

        $this->assertSame([
            'source_rows' => 6,
            'planned_new_rows' => 1,
            'finding_count' => 5,
            'finding_counts' => [
                'SOURCE_DUPLICATE' => 1,
                'ORPHAN_CHILD' => 1,
                'ALREADY_EXISTS_IN_DB' => 1,
                'INVALID_CODE' => 1,
                'MISSING_NAME' => 1,
            ],
        ], $report['summary']);

        $this->assertSame([
            [
                'kind' => 'child',
                'code' => self::CHILD_CODE,
                'name' => 'รายการใหม่',
                'source_file' => self::BUDGET_FILE,
                'source_sheet' => self::BUDGET_SHEET,
                'row_number' => 3,
                'parent_code' => self::PARENT_CODE,
            ],
        ], $report['planned_rows']);

        $this->assertSame([
            'parent' => [2],
            'child' => [5],
            'unknown' => [4, 6, 7],
        ], array_reduce(
            $report['findings'],
            static function (array $rows, array $finding): array {
                $rows[$finding['kind']][] = $finding['row_number'];

                return $rows;
            },
            ['parent' => [], 'child' => [], 'unknown' => []]
        ));

        $this->assertSame([
            'ALREADY_EXISTS_IN_DB' => 'รหัสมีอยู่แล้วในฐานข้อมูล',
            'SOURCE_DUPLICATE' => 'รหัสซ้ำในไฟล์ต้นทาง',
            'ORPHAN_CHILD' => 'ไม่พบรหัสแม่ในไฟล์ต้นทาง',
            'INVALID_CODE' => 'รูปแบบรหัสไม่ถูกต้อง',
            'MISSING_NAME' => 'ไม่มีชื่อในแถว',
        ], array_column($report['findings'], 'message', 'reason_code'));

        $this->assertSame([
            'reason_code' => 'SOURCE_DUPLICATE',
            'message' => 'รหัสซ้ำในไฟล์ต้นทาง',
            'kind' => 'unknown',
            'code' => self::CHILD_CODE,
            'name' => 'รายการซ้ำ',
            'source_file' => self::BUDGET_FILE,
            'source_sheet' => self::BUDGET_SHEET,
            'row_number' => 4,
        ], $report['findings'][1]);
    }

    public function testWriteSummaryMarksPartialImportAndCountsUnwrittenRows(): void
    {
        $partial = buildFiscalImportSummary([
            'parents_written' => 15,
            'children_written' => 74,
            'activities_written' => 15,
            'categories_skipped' => 0,
            'activities_skipped' => 0,
            'errors' => 1,
        ]);

        $this->assertStringContainsString('PARTIAL IMPORT', $partial);
        $this->assertStringContainsString('Written 15 parents, 74 children, 15 activities.', $partial);
        $this->assertStringContainsString('1 row(s) NOT written', $partial);

        $clean = buildFiscalImportSummary([
            'parents_written' => 15,
            'children_written' => 75,
            'activities_written' => 15,
            'categories_skipped' => 0,
            'activities_skipped' => 0,
            'errors' => 0,
        ]);

        $this->assertStringNotContainsString('PARTIAL', $clean);
        $this->assertStringContainsString('Errors: 0.', $clean);
    }

    /**
     * @return array<string,mixed>
     */
    private static function budgetRow(
        int $rowNumber,
        string $code,
        string $name,
        string $kind,
        ?string $reasonCode
    ): array {
        return [
            'source_file' => self::BUDGET_FILE,
            'source_sheet' => self::BUDGET_SHEET,
            'row_number' => $rowNumber,
            'code' => $code,
            'name' => $name,
            'kind' => $kind,
            'reason_code' => $reasonCode,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private static function config(): array
    {
        return [
            'budget_file' => self::BUDGET_FILE,
            'budget_sheet' => self::BUDGET_SHEET,
            'activity_file' => self::ACTIVITY_FILE,
            'activity_sheet' => self::ACTIVITY_SHEET,
            'fiscal_year' => 2569,
        ];
    }
}

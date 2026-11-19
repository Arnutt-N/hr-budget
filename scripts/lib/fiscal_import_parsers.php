<?php

declare(strict_types=1);

use App\Helpers\FiscalCode;

function fiscalImportCell(mixed $cell): string
{
    return trim((string) $cell);
}

function parseBudgetRows(array $rows, string $sourceFile, string $sourceSheet): array
{
    $parents = [];
    $children = [];
    $reportRows = [];
    $seen = [];

    foreach (array_slice($rows, 1) as $index => $row) {
        $code = fiscalImportCell($row[2] ?? '');
        $name = fiscalImportCell($row[3] ?? '');
        $kind = 'unknown';
        $reason = null;

        if ($name === '') {
            $reason = 'MISSING_NAME';
        } elseif (preg_match('/^\d{13}$/D', $code) === 1) {
            $kind = 'parent';
        } elseif (FiscalCode::isValid20($code)) {
            $kind = 'child';
        } else {
            $reason = 'INVALID_CODE';
        }

        if ($reason === null && isset($seen[$code])) {
            $reason = 'SOURCE_DUPLICATE';
        } elseif ($reason === null) {
            $seen[$code] = true;
        }

        $reportRows[] = [
            'source_file' => $sourceFile,
            'source_sheet' => $sourceSheet,
            'row_number' => $index + 2,
            'code' => $code,
            'name' => $name,
            'kind' => $kind,
            'reason_code' => $reason,
        ];

        if ($reason !== null) {
            continue;
        }

        if ($kind === 'parent') {
            $parents[$code] = $name;
        } elseif ($kind === 'child') {
            $children[$code] = $name;
        }
    }

    $reportRows = array_map(
        static function (array $reportRow) use ($parents): array {
            if ($reportRow['reason_code'] !== null || $reportRow['kind'] !== 'child') {
                return $reportRow;
            }
            $parentCode = substr((string) $reportRow['code'], 0, 13);
            if (!isset($parents[$parentCode])) {
                $reportRow['reason_code'] = 'ORPHAN_CHILD';
            }
            return $reportRow;
        },
        $reportRows
    );

    return ['parents' => $parents, 'children' => $children, 'report_rows' => $reportRows];
}

function parseActivityRows(array $rows, string $sourceFile, string $sourceSheet): array
{
    $activities = [];
    $reportRows = [];
    $seen = [];

    foreach (array_slice($rows, 4) as $index => $row) {
        $code = fiscalImportCell($row[0] ?? '');
        $name = fiscalImportCell($row[1] ?? '');
        $reason = null;

        if (!FiscalCode::isValidActivityCode($code)) {
            $reason = 'INVALID_CODE';
        } elseif ($name === '') {
            $reason = 'MISSING_NAME';
        } elseif (isset($seen[$code])) {
            $reason = 'SOURCE_DUPLICATE';
        } else {
            $seen[$code] = true;
        }

        $reportRows[] = [
            'source_file' => $sourceFile,
            'source_sheet' => $sourceSheet,
            'row_number' => $index + 5,
            'code' => $code,
            'name' => $name,
            'kind' => 'activity',
            'reason_code' => $reason,
        ];

        if ($reason === null) {
            $activities[$code] = $name;
        }
    }

    return ['map' => $activities, 'report_rows' => $reportRows];
}

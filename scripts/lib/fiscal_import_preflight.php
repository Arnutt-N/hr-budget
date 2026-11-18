<?php

declare(strict_types=1);

const FISCAL_PREFLIGHT_REASON_MESSAGES = [
    'SOURCE_DUPLICATE' => 'รหัสซ้ำในไฟล์ต้นทาง',
    'ORPHAN_CHILD' => 'ไม่พบรหัสแม่ในไฟล์ต้นทาง',
    'ALREADY_EXISTS_IN_DB' => 'รหัสมีอยู่แล้วในฐานข้อมูล',
    'INVALID_CODE' => 'รูปแบบรหัสไม่ถูกต้อง',
    'MISSING_NAME' => 'ไม่มีชื่อในแถว',
];

/**
 * Build a no-write preflight report from parsed source rows.
 *
 * @param list<array<string,mixed>> $rows
 * @param array<string,int> $existingCategoryCodes
 * @param array<string,int> $existingActivityCodes
 * @param array<string,mixed> $config
 * @return array{source: array<string,mixed>, summary: array<string,mixed>, planned_rows: list<array<string,mixed>>, findings: list<array<string,mixed>>}
 */
function buildFiscalImportPreflightReport(
    array $rows,
    array $existingCategoryCodes,
    array $existingActivityCodes,
    array $config
): array {
    $source = [
        'fiscal_year' => $config['fiscal_year'] ?? null,
        'budget_file' => $config['budget_file'] ?? '',
        'budget_sheet' => $config['budget_sheet'] ?? '',
        'activity_file' => $config['activity_file'] ?? '',
        'activity_sheet' => $config['activity_sheet'] ?? '',
    ];

    $plannedRows = [];
    $findings = [];
    $counts = array_fill_keys(array_keys(FISCAL_PREFLIGHT_REASON_MESSAGES), 0);

    foreach ($rows as $row) {
        $kind = (string) ($row['kind'] ?? '');
        $code = (string) ($row['code'] ?? '');
        $reason = $row['reason_code'] ?? null;

        if (!is_string($reason) && $code !== '') {
            $alreadyExists = ($kind === 'activity')
                ? isset($existingActivityCodes[$code])
                : isset($existingCategoryCodes[$code]);
            if ($alreadyExists) {
                $reason = 'ALREADY_EXISTS_IN_DB';
            }
        }

        if (is_string($reason) && $reason !== '') {
            $counts[$reason] = ($counts[$reason] ?? 0) + 1;
            $findings[] = fiscalImportPreflightFinding($row, $reason);
            continue;
        }

        if ($code === '') {
            $counts['INVALID_CODE'] = ($counts['INVALID_CODE'] ?? 0) + 1;
            $findings[] = fiscalImportPreflightFinding($row, 'INVALID_CODE');
            continue;
        }

        $plannedRow = [
            'kind' => $kind,
            'code' => $code,
            'name' => (string) ($row['name'] ?? ''),
            'source_file' => (string) ($row['source_file'] ?? ''),
            'source_sheet' => (string) ($row['source_sheet'] ?? ''),
            'row_number' => (int) ($row['row_number'] ?? 0),
        ];
        if ($kind === 'child') {
            $plannedRow['parent_code'] = substr($code, 0, 13);
        }
        if ($kind === 'activity') {
            $plannedRow['fiscal_year'] = $source['fiscal_year'];
        }
        $plannedRows[] = $plannedRow;
    }

    return [
        'source' => $source,
        'summary' => [
            'source_rows' => count($rows),
            'planned_new_rows' => count($plannedRows),
            'finding_count' => count($findings),
            'finding_counts' => $counts,
        ],
        'planned_rows' => $plannedRows,
        'findings' => $findings,
    ];
}

/**
 * Describe one row-level finding.
 *
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function fiscalImportPreflightFinding(array $row, string $reasonCode): array
{
    return [
        'reason_code' => $reasonCode,
        'message' => FISCAL_PREFLIGHT_REASON_MESSAGES[$reasonCode] ?? $reasonCode,
        'kind' => (string) ($row['kind'] ?? ''),
        'code' => (string) ($row['code'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'source_file' => (string) ($row['source_file'] ?? ''),
        'source_sheet' => (string) ($row['source_sheet'] ?? ''),
        'row_number' => (int) ($row['row_number'] ?? 0),
    ];
}

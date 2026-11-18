<?php
/**
 * Import Thai fiscal codes from the 16002 source spreadsheets.
 *
 * Reads 15 thirteen-digit parents and 75 twenty-digit children into
 * budget_categories, and 15 activity codes into plans. The existing taxonomy
 * is reused; no second expense table is created. All rows are keyed by the
 * fiscal code, so a second run skips everything it already inserted.
 *
 * Usage: php scripts/import_fiscal_codes.php --database=NAME [--dry-run] [--verbose] [--help]
 *   --database=NAME  Required. Must end in _test, so production writes are impossible.
 *   --dry-run        Read the files, print the preflight report, write nothing.
 *   --verbose        Print row-level findings and written or skipped rows.
 *   --help           Print this message and exit.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Core\Database;
use App\Helpers\FiscalCode;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . '/lib/fiscal_import_preflight.php';
require_once __DIR__ . '/lib/fiscal_import_parsers.php';

$options = getopt('', ['dry-run', 'verbose', 'help', 'database::']);

if (isset($options['help'])) {
    echo "Fiscal code import script\n";
    echo "Usage: php scripts/import_fiscal_codes.php --database=NAME [OPTIONS]\n\n";
    echo "Options:\n";
    echo "  --database=NAME  Required. Target database, must end in _test.\n";
    echo "  --dry-run        Print a preflight report without writing.\n";
    echo "  --verbose        Print row-level findings and written or skipped rows.\n";
    echo "  --help           Show this help message.\n";
    exit(0);
}

$configFile = __DIR__ . '/../config/import_fiscal_codes.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "[ERROR] Configuration file not found: {$configFile}\n");
    exit(1);
}
$config = require $configFile;

$isDryRun = isset($options['dry-run']);
$isVerbose = isset($options['verbose']);

// The database name is a write boundary. Require it explicitly and reject
// anything that is not a test database before opening a connection.
$database = $options['database'] ?? null;
if (!is_string($database) || $database === '' || !str_ends_with($database, '_test')) {
    fwrite(STDERR, "[ERROR] Refusing to run: --database=NAME is required and NAME must end in _test.\n");
    exit(1);
}

// Populate $_ENV so config/database.php can be read after the guard above.
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
$_ENV['DB_DATABASE'] = $database;

set_time_limit(0);

/**
 * Read one named sheet as an array of rows.
 *
 * @return list<list<mixed>>
 */
function readSheet(string $file, string $sheetName): array
{
    if (!is_file($file)) {
        throw new RuntimeException("Source file not found: {$file}");
    }

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getSheetByName($sheetName);
    if ($sheet === null) {
        throw new RuntimeException("Sheet '{$sheetName}' not found in {$file}");
    }

    return $sheet->toArray(null, true, true, false);
}

/**
 * Existing fiscal codes, so a repeat run writes nothing.
 *
 * @param list<string> $codes
 * @return array<string,int> Set of codes already present.
 */
function existingFiscalCodes(array $codes): array
{
    if ($codes === []) {
        return [];
    }

    $found = [];
    foreach (array_chunk($codes, 500) as $chunk) {
        $marks = implode(',', array_fill(0, count($chunk), '?'));
        $rows = Database::query(
            "SELECT fiscal_code FROM budget_categories WHERE fiscal_code IN ({$marks})",
            $chunk
        );
        foreach ($rows as $row) {
            $found[(string) $row['fiscal_code']] = 1;
        }
    }

    return $found;
}

/**
 * Existing activity codes, so a repeat run writes nothing.
 *
 * @param list<string> $codes
 * @return array<string,int> Set of codes already present.
 */
function existingActivityCodes(array $codes): array
{
    if ($codes === []) {
        return [];
    }

    $found = [];
    foreach (array_chunk($codes, 500) as $chunk) {
        $marks = implode(',', array_fill(0, count($chunk), '?'));
        $rows = Database::query(
            "SELECT activity_code FROM plans WHERE activity_code IN ({$marks})",
            $chunk
        );
        foreach ($rows as $row) {
            $found[(string) $row['activity_code']] = 1;
        }
    }

    return $found;
}

try {
    $budget = parseBudgetRows(
        readSheet($config['budget_file'], $config['budget_sheet']),
        (string) $config['budget_file'],
        (string) $config['budget_sheet']
    );
    $activities = parseActivityRows(
        readSheet($config['activity_file'], $config['activity_sheet']),
        (string) $config['activity_file'],
        (string) $config['activity_sheet']
    );
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    exit(1);
}

$parents = $budget['parents'];
$children = $budget['children'];

if ($isDryRun) {
    try {
        $existingCategories = existingFiscalCodes(array_merge(
            array_keys($parents),
            array_keys($children)
        ));
        $existingActivities = existingActivityCodes(array_keys($activities['map']));
    } catch (Throwable $e) {
        fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
        exit(1);
    }

    $report = buildFiscalImportPreflightReport(
        array_merge($budget['report_rows'], $activities['report_rows']),
        $existingCategories,
        $existingActivities,
        $config
    );

    echo sprintf("ปีแหล่งที่มา: %d\n", $report['source']['fiscal_year']);
    echo sprintf("ไฟล์งบ: %s (%s)\n", $report['source']['budget_file'], $report['source']['budget_sheet']);
    echo sprintf("ไฟล์กิจกรรม: %s (%s)\n", $report['source']['activity_file'], $report['source']['activity_sheet']);
    echo sprintf(
        "ตรวจ %d แถว: อ่าน %d แถว, สร้างใหม่ %d แถว, พบปัญหา %d รายการ\n",
        $report['summary']['source_rows'],
        count($parents) + count($children) + count($activities['map']),
        $report['summary']['planned_new_rows'],
        $report['summary']['finding_count']
    );
    foreach ($report['summary']['finding_counts'] as $reasonCode => $count) {
        echo sprintf("%s: %d\n", $reasonCode, $count);
    }
    if ($isVerbose) {
        foreach ($report['findings'] as $finding) {
            echo sprintf(
                "%s | %s | %s | %s:%d | %s\n",
                $finding['reason_code'],
                $finding['message'],
                $finding['code'],
                $finding['source_file'],
                $finding['row_number'],
                $finding['name']
            );
        }
    }
    echo "[DRY-RUN] ไม่มีการเขียนข้อมูล\n";
    exit(0);
}

echo sprintf(
    "Read %d rows: %d parents, %d children, %d activities.\n",
    count($parents) + count($children),
    count($parents),
    count($children),
    count($activities['map'])
);

$stats = [
    'parents_written' => 0,
    'children_written' => 0,
    'activities_written' => 0,
    'categories_skipped' => 0,
    'activities_skipped' => 0,
    'errors' => 0,
];

Database::beginTransaction();

try {
    $existingCategories = existingFiscalCodes(array_merge(
        array_keys($parents),
        array_keys($children)
    ));
    $existingActivities = existingActivityCodes(array_keys($activities['map']));

    // Insert parents first so every child can resolve its parent id.
    $parentIds = [];
    foreach ($existingCategories as $code => $unused) {
        if (isset($parents[$code])) {
            $row = Database::queryOne(
                'SELECT id FROM budget_categories WHERE fiscal_code = ?',
                [$code]
            );
            if ($row !== null) {
                $parentIds[$code] = (int) $row['id'];
            }
        }
    }

    foreach ($parents as $code => $name) {
        if (isset($existingCategories[$code])) {
            $stats['categories_skipped']++;
            if ($isVerbose) {
                echo "SKIP category {$code}\n";
            }
            continue;
        }

        $id = Database::insert('budget_categories', [
            'code' => $code,
            'name_th' => $name,
            'fiscal_code' => $code,
            'level' => 0,
        ]);
        $parentIds[$code] = $id;
        $stats['parents_written']++;
        if ($isVerbose) {
            echo "INSERT parent {$code}\n";
        }
    }

    foreach ($children as $code => $name) {
        if (isset($existingCategories[$code])) {
            $stats['categories_skipped']++;
            if ($isVerbose) {
                echo "SKIP category {$code}\n";
            }
            continue;
        }

        $parentCode = FiscalCode::parent13($code);
        if (!isset($parentIds[$parentCode])) {
            $stats['errors']++;
            fwrite(STDERR, "[ERROR] No parent row for child {$code}\n");
            continue;
        }

        Database::insert('budget_categories', [
            'code' => $code,
            'name_th' => $name,
            'fiscal_code' => $code,
            'fiscal_code_parent' => $parentCode,
            'expense_cat_code' => FiscalCode::expenseCategory($code),
            'parent_id' => $parentIds[$parentCode],
            'level' => 1,
        ]);
        $stats['children_written']++;
        if ($isVerbose) {
            echo "INSERT child {$code}\n";
        }
    }

    $sortOrder = 0;
    foreach ($activities['map'] as $code => $name) {
        if (isset($existingActivities[$code])) {
            $stats['activities_skipped']++;
            if ($isVerbose) {
                echo "SKIP activity {$code}\n";
            }
            continue;
        }

        Database::insert('plans', [
            'activity_code' => $code,
            'name_th' => $name,
            'code' => $code,
            'fiscal_year' => $config['fiscal_year'],
            'budget_type_id' => null,
            'sort_order' => $sortOrder,
        ]);
        $sortOrder++;
        $stats['activities_written']++;
        if ($isVerbose) {
            echo "INSERT activity {$code}\n";
        }
    }

    Database::commit();
} catch (Throwable $e) {
    Database::rollback();
    fwrite(STDERR, '[ERROR] Import failed, all writes rolled back: ' . $e->getMessage() . "\n");
    exit(1);
}

echo sprintf(
    "Written %d parents, %d children, %d activities. Skipped %d categories, %d activities. Errors: %d.\n",
    $stats['parents_written'],
    $stats['children_written'],
    $stats['activities_written'],
    $stats['categories_skipped'],
    $stats['activities_skipped'],
    $stats['errors']
);

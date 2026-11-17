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
 *   --dry-run        Read the files, print planned counts, write nothing.
 *   --verbose        Print each planned or skipped row.
 *   --help           Print this message and exit.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Core\Database;
use App\Helpers\FiscalCode;
use PhpOffice\PhpSpreadsheet\IOFactory;

$options = getopt('', ['dry-run', 'verbose', 'help', 'database::']);

if (isset($options['help'])) {
    echo "Fiscal code import script\n";
    echo "Usage: php scripts/import_fiscal_codes.php --database=NAME [OPTIONS]\n\n";
    echo "Options:\n";
    echo "  --database=NAME  Required. Target database, must end in _test.\n";
    echo "  --dry-run        Preview counts without writing.\n";
    echo "  --verbose        Print each planned or skipped row.\n";
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
 * Normalise a spreadsheet cell to a trimmed UTF-8 string.
 */
function cellString(mixed $cell): string
{
    return trim((string) $cell);
}

/**
 * Parse the budget sheet into 13-digit parents and 20-digit children.
 *
 * @param list<list<mixed>> $rows
 * @return array{parents: array<string,string>, children: array<string,string>}
 */
function parseBudgetRows(array $rows): array
{
    $parents = [];
    $children = [];

    foreach (array_slice($rows, 1) as $row) {
        $code = cellString($row[2] ?? '');
        $name = cellString($row[3] ?? '');
        if ($name === '') {
            continue;
        }

        if (preg_match('/^\d{13}$/D', $code) === 1) {
            $parents[$code] = $name;
        } elseif (FiscalCode::isValid20($code)) {
            $children[$code] = $name;
        }
    }

    return ['parents' => $parents, 'children' => $children];
}

/**
 * Parse the activity sheet. Title rows 1-3 and the header row 4 are skipped.
 *
 * @param list<list<mixed>> $rows
 * @return array<string,string> Map of 17-digit code to description.
 */
function parseActivityRows(array $rows): array
{
    $activities = [];

    foreach (array_slice($rows, 4) as $row) {
        $code = cellString($row[0] ?? '');
        $name = cellString($row[1] ?? '');
        if (FiscalCode::isValidActivityCode($code) && $name !== '') {
            $activities[$code] = $name;
        }
    }

    return $activities;
}

/**
 * Existing fiscal codes, so a repeat run writes nothing.
 *
 * @param list<string> $codes
 * @return array<string,int> Set of codes already present.
 */
function existingFiscalCodes(array $codes): array
{
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
    $budget = parseBudgetRows(readSheet($config['budget_file'], $config['budget_sheet']));
    $activities = parseActivityRows(readSheet($config['activity_file'], $config['activity_sheet']));
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    exit(1);
}

$parents = $budget['parents'];
$children = $budget['children'];

echo sprintf(
    "Read %d rows: %d parents, %d children, %d activities.\n",
    count($parents) + count($children),
    count($parents),
    count($children),
    count($activities)
);

if ($isDryRun) {
    echo "[DRY-RUN] No rows written.\n";
    exit(0);
}

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
    $existingActivities = existingActivityCodes(array_keys($activities));

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
    foreach ($activities as $code => $name) {
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

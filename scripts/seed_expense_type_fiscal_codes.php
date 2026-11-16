<?php
/**
 * Repair the mojibake name_th values on expense_types.
 *
 * Part of the fiscal-code import program (PR1). The 5 existing expense_types
 * rows store their Thai name as literal '?' bytes (0x3F) — an encoding debt
 * from a prior bad import. This script repairs them by updating name_th with
 * clean UTF-8 literals, keyed by code (which is stable and readable) rather
 * than by name_th (which is the corrupted column).
 *
 * Idempotent: only updates rows whose name_th does not already match the
 * expected clean value, so a second run reports "already correct" for every
 * row and changes nothing.
 *
 * Usage:  php scripts/seed_expense_type_fiscal_codes.php --database=hr_budget_test
 *
 * Safe by construction: the script exits(1) unless the resolved database name
 * ends in _test. Shell env vars do NOT reach $_ENV here (variables_order=GPCS),
 * so use --database=NAME to select the target explicitly.
 *
 * Requires: vendor/ autoload, config/database.php (DB_* env or defaults),
 *           the expense_types table already migrated to 094.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// config/database.php reads $_ENV, so load .env before anything resolves it.
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

require_once __DIR__ . '/lib/fiscal_seed_guard.php';

use App\Core\Database;

// Parse the explicit CLI override before config is loaded.
$databaseOption = null;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--database=')) {
        $databaseOption = substr($arg, strlen('--database='));
    }
}

if ($databaseOption !== null) {
    $optionError = fiscalSeedDatabaseError($databaseOption);
    if ($optionError !== null) {
        fwrite(STDERR, "Error: " . $optionError . "\n");
        exit(1);
    }
    $_ENV['DB_DATABASE'] = $databaseOption;
}

// Resolve the database exactly as Database::connect() does, but before writes.
$config = require dirname(__DIR__) . '/config/database.php';
$configError = fiscalSeedDatabaseError((string) $config['database']);
if ($configError !== null) {
    fwrite(STDERR, "Error: " . $configError . "\n");
    exit(1);
}

// Expected clean names keyed by the stable code column.
// These are the 5 expense categories that existed before the fiscal-code
// import (the 3 new rows 094 seeds carry their correct name_th at insert time
// and are not touched here).
$expected = [
    'ET-dda6' => 'งบบุคลากร',
    'ET-57d1' => 'งบดำเนินงาน',
    'ET-914f' => 'งบลงทุน',
    'ET-dc9b' => 'งบรายจ่ายอื่น',
    'ET-8d6b' => 'งบเงินอุดหนุน',
];

$updated = 0;
$already = 0;
$missing = 0;

try {
    foreach ($expected as $code => $cleanName) {
        $row = Database::queryOne(
            'SELECT name_th FROM expense_types WHERE code = ?',
            [$code]
        );

        if ($row === null) {
            echo "  [missing] {$code} not found — skipped\n";
            $missing++;
            continue;
        }

        if ($row['name_th'] === $cleanName) {
            echo "  [ok]      {$code} already correct — {$cleanName}\n";
            $already++;
            continue;
        }

        $affected = Database::update(
            'expense_types',
            ['name_th' => $cleanName],
            'code = ?',
            [$code]
        );

        if ($affected > 0) {
            echo "  [fixed]   {$code} → {$cleanName}\n";
            $updated++;
        } else {
            echo "  [ok]      {$code} already correct\n";
            $already++;
        }
    }

    echo "\nSummary: {$updated} updated, {$already} already correct, {$missing} missing\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
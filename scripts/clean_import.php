<?php
/**
 * Clean Import - Clear ALL FY 2569 data and re-import fresh
 */

echo "=== CLEAN IMPORT (FY 2569) ===\n\n";

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Step 1: Disable foreign key checks
echo "Step 1: Disabling foreign key checks...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Step 2: Clean up FY 2569 data
echo "\nStep 2: Cleaning up old FY 2569 data...\n";

$tables = [
    'budget_line_items' => 'fiscal_year = 2569',
    'budget_plans' => 'fiscal_year = 2569',
    'activities' => 'fiscal_year = 2569',
    'projects' => 'fiscal_year = 2569',
    'plans' => 'fiscal_year = 2569',
    'budget_types' => '1=1', // Clean all
    'expense_items' => '1=1',
    'expense_groups' => '1=1',
    'expense_types' => '1=1',
];

foreach ($tables as $table => $condition) {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM $table WHERE $condition");
    $before = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    $pdo->exec("DELETE FROM $table WHERE $condition");
    
    echo "  ✓ Cleaned $table: $before rows deleted\n";
}

// Don't delete organizations - they might be used elsewhere
echo "  ⚠ Keeping organizations table (not deleted)\n";

// Step 3: Re-enable foreign key checks
echo "\nStep 3: Re-enabling foreign key checks...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== CLEANUP COMPLETE ===\n\n";
echo "Now running import script...\n\n";
echo str_repeat("=", 60) . "\n\n";

// Step 4: Run import script
require_once __DIR__ . '/scripts/import_budget_csv.php';

echo "\n\n" . str_repeat("=", 60) . "\n\n";
echo "Now running sync script...\n\n";

// Step 5: Run sync script
require_once __DIR__ . '/sync_v6.php';

echo "\n\n=== ALL DONE ===\n";
echo "Data cleaned and re-imported successfully!\n";
echo "\nNext step: Fix session linkage\n";
echo "Run: php fix_cli.php\n";

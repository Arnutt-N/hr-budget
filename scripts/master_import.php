<?php
// Master Import Script - Cleans everything and re-imports correctly
echo "=== MASTER CLEAN IMPORT (FY 2569) ===\n\n";

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET NAMES utf8mb4");

// 1. Clean Data
echo "1. Cleaning old data...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['budget_line_items', 'budget_plans', 'activities', 'projects', 'plans'];
foreach ($tables as $table) {
    $pdo->exec("DELETE FROM $table WHERE fiscal_year = 2569");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "   ✓ Cleared " . implode(', ', $tables) . "\n\n";

// 2. Import CSV (This uses the V4 script which now uses correct fix_encoding)
echo "2. Running Import Script...\n";
// Ensure we use the correct CSV path and fix_encoding logic
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/public'; // Fake doc root
require_once __DIR__ . '/scripts/import_budget_csv.php';
echo "\n   ✓ Import completed.\n\n";

// 3. Sync Budget Plans
echo "3. Running Sync (v6) Script...\n";
require_once __DIR__ . '/sync_v6.php';
echo "\n   ✓ Sync completed.\n\n";

// 4. Fix Session 6 Linkage
echo "4. Fixing Session Linkage...\n";
$stmt = $pdo->query(
    "SELECT bli.division_id, COUNT(*) as cnt 
     FROM budget_line_items bli 
     WHERE fiscal_year = 2569 
     GROUP BY bli.division_id 
     ORDER BY cnt DESC 
     LIMIT 1"
);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $targetOrgId = $result['division_id'];
    $pdo->query("UPDATE disbursement_sessions SET organization_id = $targetOrgId WHERE id = 6");
    
    // Get Org Name
    $orgName = $pdo->query("SELECT name_th FROM organizations WHERE id = $targetOrgId")->fetchColumn();
    
    echo "   ✓ Linked Session 6 to Org ID: $targetOrgId ($orgName)\n";
    echo "   ✓ Item Count: {$result['cnt']}\n";
} else {
    echo "   ⚠ WARNING: No budget items found to link!\n";
}

echo "\n=== ALL DONE ===\n";
echo "Verify at: http://localhost/hr_budget/public/budgets/tracking/activities?session_id=6\n";

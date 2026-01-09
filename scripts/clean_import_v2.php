<?php
/**
 * Clean Import (V2) - Enhanced logging and integrity checks
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CLEAN IMPORT V2 (FY 2569) ===\n\n";

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("DB Connection Error: " . $e->getMessage() . "\n");
}

// Check if import script exists
if (!file_exists(__DIR__ . '/scripts/import_budget_csv.php')) {
    die("Error: scripts/import_budget_csv.php not found!\n");
}

// 1. Clean
echo "Step 1: Cleaning old data...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['budget_line_items', 'budget_plans', 'activities', 'projects', 'plans'];
foreach ($tables as $table) {
    $pdo->exec("DELETE FROM $table WHERE fiscal_year = 2569");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "  ✓ Deleted FY 2569 data from " . implode(', ', $tables) . "\n\n";

// 2. Import
echo "Step 2: Running Import Script...\n";
ob_start();
include __DIR__ . '/scripts/import_budget_csv.php';
$importOutput = ob_get_clean();
echo $importOutput; // Show output
echo "\n  ✓ Import script finished.\n\n";

// 3. Verify Encoding immediatley
echo "Step 3: Verifying Encoding (Sample)...\n";
$stmt = $pdo->query("SELECT name_th FROM activities WHERE fiscal_year = 2569 LIMIT 1");
$name = $stmt->fetchColumn();
echo "  Sample Activity: $name\n";
echo "  Hex: " . bin2hex($name) . "\n";
if (strpos($name, '?') !== false) {
    echo "  ⚠️ WARNING: Question marks detected! Import might have failed encoding.\n";
} else {
    echo "  ✓ Encoding looks okay (no question marks).\n";
}
echo "\n";

// 4. Sync
echo "Step 4: Running Sync Script...\n";
ob_start();
include __DIR__ . '/sync_v6.php';
$syncOutput = ob_get_clean();
// Clean HTML tags for CLI
$syncOutputClean = strip_tags(str_replace(['<p>', '<h2>', '<h1>'], ["\n", "\n== ", "\n= "], $syncOutput));
echo $syncOutputClean;
echo "\n  ✓ Sync script finished.\n\n";

// 5. Final Count
$cnt = $pdo->query("SELECT COUNT(*) FROM budget_plans WHERE fiscal_year = 2569")->fetchColumn();
echo "Step 5: Final Check\n";
echo "  Total budget_plans: $cnt\n\n";
echo "=== DONE ===\n";
echo "Run 'php fix_cli.php' to fix session linkage.\n";

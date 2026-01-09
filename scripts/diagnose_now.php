<?php
// Quick diagnostic after clean import
$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== POST-IMPORT DIAGNOSTIC ===\n\n";

// 1. Check tables
echo "1. Table Counts:\n";

// Tables with fiscal_year
$fyTables = ['plans', 'projects', 'activities', 'budget_line_items', 'budget_plans'];
foreach ($fyTables as $table) {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM $table WHERE fiscal_year = 2569");
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    echo "   $table: $cnt\n";
}

// Tables without fiscal_year
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM budget_types");
$cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
echo "   budget_types: $cnt (all)\n";

// 2. budget_line_items details
echo "\n2. Budget Line Items (Org 111):\n";
$stmt = $pdo->query("SELECT COUNT(*) as cnt, COUNT(DISTINCT activity_id) as acts FROM budget_line_items WHERE division_id = 111");
$bli = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   Total: {$bli['cnt']}\n";
echo "   Unique activity_ids: {$bli['acts']}\n";

// 3. Check if activity_ids exist in budget_plans
echo "\n3. Activity IDs validation:\n";
$stmt = $pdo->query(
    "SELECT bli.activity_id, bp.id as bp_id, bp.name_th
     FROM budget_line_items bli
     LEFT JOIN budget_plans bp ON bli.activity_id = bp.id
     WHERE bli.division_id = 111
     LIMIT 5"
);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $row['bp_id'] ? "✓ {$row['name_th']}" : "✗ NOT IN budget_plans";
    echo "   Activity {$row['activity_id']}: $status\n";
}

// 4. Controller filter simulation
echo "\n4. Controller Filter Simulation:\n";
$stmt = $pdo->query(
    "SELECT DISTINCT bli.activity_id 
     FROM budget_line_items bli
     WHERE bli.division_id = 111 AND bli.fiscal_year = 2569"
);
$allowedIds = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $allowedIds[] = $row['activity_id'];
}
echo "   Allowed Activity IDs: " . implode(', ', $allowedIds) . "\n";

if (empty($allowedIds)) {
    echo "   ERROR: No activity IDs found!\n";
} else {
    $placeholders = implode(',', array_fill(0, count($allowedIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) as cnt FROM budget_plans 
         WHERE id IN ($placeholders) AND fiscal_year = 2569"
    );
    $stmt->execute($allowedIds);
    $matchCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    echo "   Matching budget_plans: $matchCount / " . count($allowedIds) . "\n";
    
    if ($matchCount == 0) {
        echo "   ❌ PROBLEM: None of the activity_ids exist in budget_plans!\n";
        echo "   Need to run: php sync_missing_activities.php\n";
    } else {
        echo "   ✓ Some activities found in budget_plans\n";
    }
}

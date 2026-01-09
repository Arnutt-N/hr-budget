<?php
$config = include 'config/database.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);

echo "=== Fixing division_id = 3 data ===" . PHP_EOL;

// Get plan_id for "แผนงานบุคลากรภาครัฐ"
$correct_plan_id = $pdo->query("SELECT id FROM plans WHERE name_th = 'แผนงานบุคลากรภาครัฐ'")->fetchColumn();
echo "Correct plan_id: $correct_plan_id" . PHP_EOL;

// Count records before
$before = $pdo->query("SELECT COUNT(*) FROM budget_line_items WHERE division_id = 3")->fetchColumn();
echo "Records before: $before" . PHP_EOL;

// Update incorrect records (set division_id = NULL for plans that don't belong to division 3)
$pdo->beginTransaction();
$sql = "UPDATE budget_line_items SET division_id = NULL WHERE division_id = 3 AND plan_id != ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$correct_plan_id]);
$updated = $stmt->rowCount();
$pdo->commit();

echo "Updated to NULL: $updated rows" . PHP_EOL;

// Count records after
$after = $pdo->query("SELECT COUNT(*) FROM budget_line_items WHERE division_id = 3")->fetchColumn();
echo "Records remaining with division_id = 3: $after" . PHP_EOL;

$null_count = $pdo->query("SELECT COUNT(*) FROM budget_line_items WHERE division_id IS NULL")->fetchColumn();
echo "Records with division_id = NULL: $null_count" . PHP_EOL;

// Verify remaining plans for division_id = 3
echo PHP_EOL . "=== Plans for division_id = 3 ===" . PHP_EOL;
$div3 = $pdo->query("
    SELECT DISTINCT p.name_th, COUNT(*) as cnt 
    FROM budget_line_items b 
    JOIN plans p ON b.plan_id = p.id 
    WHERE b.division_id = 3 
    GROUP BY p.name_th
")->fetchAll(PDO::FETCH_ASSOC);

foreach($div3 as $r) {
    echo "- {$r['name_th']} ({$r['cnt']} rows)" . PHP_EOL;
}

// Verify plans with NULL division_id
echo PHP_EOL . "=== Plans with division_id = NULL ===" . PHP_EOL;
$null_plans = $pdo->query("
    SELECT DISTINCT p.name_th, COUNT(*) as cnt 
    FROM budget_line_items b 
    JOIN plans p ON b.plan_id = p.id 
    WHERE b.division_id IS NULL 
    GROUP BY p.name_th
")->fetchAll(PDO::FETCH_ASSOC);

foreach($null_plans as $r) {
    echo "- {$r['name_th']} ({$r['cnt']} rows)" . PHP_EOL;
}

echo PHP_EOL . "✅ Data fixed successfully!" . PHP_EOL;

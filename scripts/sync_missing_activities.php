<?php
// Sync ALL activities to budget_plans
$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== SYNC ACTIVITIES TO BUDGET_PLANS ===\n\n";

// Get missing activities
$stmt = $pdo->query(
    "SELECT a.* 
     FROM activities a
     LEFT JOIN budget_plans bp ON a.id = bp.id AND bp.plan_type = 'activity'
     WHERE a.fiscal_year = 2569 AND bp.id IS NULL"
);
$missing = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found {count($missing)} missing activities\n";

if (count($missing) == 0) {
    echo "All activities already synced!\n";
    exit(0);
}

echo "Inserting missing activities...\n";

$inserted = 0;
foreach ($missing as $act) {
    // Insert into budget_plans
    $sql = "INSERT INTO budget_plans (
        id, parent_id, plan_type, name_th, name_en, 
        code, fiscal_year, created_at, updated_at
    ) VALUES (?, ?, 'activity', ?, ?, ?, ?, NOW(), NOW())";
    
    $pdo->prepare($sql)->execute([
        $act['id'],
        $act['project_id'], // parent is project
        $act['name_th'],
        $act['name_en'],
        $act['code'],
        $act['fiscal_year']
    ]);
    
    $inserted++;
    echo "  ✓ Inserted Activity ID {$act['id']}: {$act['name_th']}\n";
}

echo "\n=== SYNC COMPLETE ===\n";
echo "Inserted: $inserted activities\n";

// Verify
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM budget_plans WHERE plan_type = 'activity' AND fiscal_year = 2569");
$total = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total activities in budget_plans now: {$total['cnt']}\n";

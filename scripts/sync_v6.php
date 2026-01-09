<?php
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Syncing Budget Structure (v6 - No Txn)</h1>";

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE budget_plans");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>Truncated budget_plans.</p>";

    $planMap = []; 
    $projectMap = [];
    $FY = 2569;

    // Plans
    $stmt = $pdo->query("SELECT * FROM plans ORDER BY sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql = "INSERT INTO budget_plans (plan_type, code, name_th, fiscal_year, level) VALUES ('program', ?, ?, ?, 1)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$row['code'], $row['name_th'], $FY]);
        $bpId = $pdo->lastInsertId();
        $planMap[$row['id']] = $bpId;
    }
    echo "<p>Synced Plans: " . count($planMap) . "</p>";

    // Projects
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parentBpId = isset($planMap[$row['plan_id']]) ? $planMap[$row['plan_id']] : null;
        if (!$parentBpId) {
             echo "<p style='color:orange'>Skipped Project ID {$row['id']} (Code: {$row['code']}): Parent Plan ID {$row['plan_id']} not found in map.</p>";
             continue;
        }
        
        $sql = "INSERT INTO budget_plans (parent_id, plan_type, code, name_th, fiscal_year, level) VALUES (?, 'project', ?, ?, ?, 2)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$parentBpId, $row['code'], $row['name_th'], $FY]);
        $bpId = $pdo->lastInsertId();
        $projectMap[$row['id']] = $bpId;
    }
    echo "<p>Synced Projects: " . count($projectMap) . "</p>";

    // Activities
    $stmt = $pdo->query("SELECT * FROM activities ORDER BY sort_order");
    $actCount = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parentBpId = null;
        if ($row['project_id']) {
            $parentBpId = isset($projectMap[$row['project_id']]) ? $projectMap[$row['project_id']] : null;
        } elseif ($row['plan_id']) {
            $parentBpId = isset($planMap[$row['plan_id']]) ? $planMap[$row['plan_id']] : null;
        }

        if (!$parentBpId) {
            echo "<p style='color:red'>Skipped Activity ID {$row['id']} (Code: {$row['code']}): Parent Project/Plan not found. ProjectID: {$row['project_id']}, PlanID: {$row['plan_id']}</p>";
            continue;
        }

        $sql = "INSERT INTO budget_plans (parent_id, plan_type, code, name_th, fiscal_year, level) VALUES (?, 'activity', ?, ?, ?, 3)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$parentBpId, $row['code'], $row['name_th'], $FY]);
        $actCount++;
    }
    echo "<p>Synced Activities: $actCount</p>";

    $cnt = $pdo->query("SELECT COUNT(*) FROM budget_plans")->fetchColumn();
    echo "<h2>Final DB Count: $cnt</h2>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
}

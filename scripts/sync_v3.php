<?php
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Syncing Budget Structure (v3)</h1>";

try {
    $pdo->beginTransaction();

    $cnt = $pdo->query("SELECT COUNT(*) FROM disbursement_records")->fetchColumn();
    if ($cnt > 0) {
        $pdo->rollBack();
        die("Cannot truncate budget_plans: data exists.");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE budget_plans");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>Truncated budget_plans.</p>";

    $planMap = []; 
    $projectMap = [];

    // 2. Plans
    $stmt = $pdo->query("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql = "INSERT INTO budget_plans (plan_type, code, name_th, fiscal_year, level) VALUES ('program', ?, ?, ?, 1)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$row['code'], $row['name_th'], $row['fiscal_year']]);
        $bpId = $pdo->lastInsertId();
        $planMap[$row['id']] = $bpId;
    }
    echo "<p>Synced Plans: " . count($planMap) . "</p>";

    // 3. Projects
    $stmt = $pdo->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parentBpId = isset($planMap[$row['plan_id']]) ? $planMap[$row['plan_id']] : null;
        if (!$parentBpId) continue;
        
        // Force map 'output' to 'project' because ENUM doesn't support 'output'
        $bType = 'project'; 
        
        $sql = "INSERT INTO budget_plans (parent_id, plan_type, code, name_th, fiscal_year, level) VALUES (?, ?, ?, ?, ?, 2)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$parentBpId, $bType, $row['code'], $row['name_th'], $row['fiscal_year']]);
        $bpId = $pdo->lastInsertId();
        $projectMap[$row['id']] = $bpId;
    }
    echo "<p>Synced Projects: " . count($projectMap) . "</p>";

    // 4. Activities
    $stmt = $pdo->query("SELECT * FROM activities WHERE is_active = 1 ORDER BY sort_order");
    $actCount = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parentBpId = null;
        if ($row['project_id']) {
            $parentBpId = isset($projectMap[$row['project_id']]) ? $projectMap[$row['project_id']] : null;
        } elseif ($row['plan_id']) {
            $parentBpId = isset($planMap[$row['plan_id']]) ? $planMap[$row['plan_id']] : null;
        }

        if (!$parentBpId) continue;

        $sql = "INSERT INTO budget_plans (parent_id, plan_type, code, name_th, fiscal_year, level) VALUES (?, 'activity', ?, ?, ?, 3)";
        $ins = $pdo->prepare($sql);
        $ins->execute([$parentBpId, $row['code'], $row['name_th'], $row['fiscal_year']]);
        $actCount++;
    }
    echo "<p>Synced Activities: $actCount</p>";

    $pdo->commit();
    echo "<h2 style='color:green'>Sync Complete</h2>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
}

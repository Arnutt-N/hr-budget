<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$db = \App\Core\Database::getInstance();

$orgId = 3;
$year = 2569;

ob_start();

echo "Diagnostic Plan for Org ID: $orgId, Year: $year\n";
echo "=============================================\n\n";

// 1. Check if ANY budget exists for this org in ANY year
echo "1. Checking presence in budget_line_items across all years:\n";
$sql = "SELECT fiscal_year, COUNT(*) as rows, SUM(allocated_received) as total_received 
        FROM budget_line_items WHERE division_id = ? GROUP BY fiscal_year";
$stmt = $db->prepare($sql);
$stmt->execute([$orgId]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

// 2. Check Plan 15 specifically
echo "\n2. Activities belonging to Plan 15 (แผนงานบุคลากรภาครัฐ):\n";
$sql = "SELECT a.id, a.name_th as act_name, pj.name_th as proj_name
        FROM activities a
        JOIN projects pj ON a.project_id = pj.id
        WHERE pj.plan_id = 15";
$stmt = $db->query($sql);
$plan15Acts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($plan15Acts as $act) {
    echo " - ID: {$act['id']} | {$act['act_name']} (Proj: {$act['proj_name']})\n";
}

// 3. Check if these Plan 15 activities are linked to Org 3 in budget_line_items
echo "\n3. Intersection of Plan 15 activities and Org 3 in budget_line_items:\n";
$actIds = array_column($plan15Acts, 'id');
if (!empty($actIds)) {
    $placeholders = implode(',', array_fill(0, count($actIds), '?'));
    $sql = "SELECT activity_id, fiscal_year, allocated_received 
            FROM budget_line_items 
            WHERE division_id = ? AND activity_id IN ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge([$orgId], $actIds));
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 4. Check budget_allocations again with relaxed filters
echo "\n4. Checking budget_allocations for Org 3 (all years):\n";
$sql = "SELECT fiscal_year, COUNT(*) as rows FROM budget_allocations WHERE organization_id = ? GROUP BY fiscal_year";
$stmt = $db->prepare($sql);
$stmt->execute([$orgId]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

// 5. Check if Org 3 has a PARENT organization that might hold the budget
echo "\n5. Organization 3 details and parent:\n";
$sql = "SELECT id, name_th, parent_id FROM organizations WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$orgId]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($org);

if ($org && $org['parent_id']) {
    echo "\nParent Details (ID: {$org['parent_id']}):\n";
    $sql = "SELECT id, name_th FROM organizations WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$org['parent_id']]);
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
    
    echo "\nChecking budget in Parent org for year $year:\n";
    $sql = "SELECT COUNT(*) FROM budget_allocations WHERE organization_id = ? AND fiscal_year = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$org['parent_id'], $year]);
    echo "Rows in budget_allocations: " . $stmt->fetchColumn() . "\n";
}

$output = ob_get_clean();
file_put_contents(__DIR__ . '/diagnose_result.txt', $output);
echo "Result written to " . __DIR__ . '/diagnose_result.txt';

<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$db = \App\Core\Database::getInstance();

$orgId = 3;
$year = 2569;

echo "Comparison for Org ID: $orgId, Year: $year\n";
echo "==========================================\n\n";

echo "1. budget_line_items (using division_id):\n";
$sql1 = "SELECT DISTINCT pl.name_th as plan_name 
         FROM budget_line_items bli 
         JOIN plans pl ON bli.plan_id = pl.id 
         WHERE bli.division_id = ? AND bli.fiscal_year = ?";
$stmt1 = $db->prepare($sql1);
$stmt1->execute([$orgId, $year]);
$plans1 = $stmt1->fetchAll(PDO::FETCH_COLUMN);
foreach ($plans1 as $p) echo " - $p\n";

echo "\n2. budget_allocations (using organization_id):\n";
$sql2 = "SELECT DISTINCT pl.name_th as plan_name 
         FROM budget_allocations ba 
         JOIN plans pl ON ba.plan_id = pl.id 
         WHERE ba.organization_id = ? AND ba.fiscal_year = ?";
$stmt2 = $db->prepare($sql2);
$stmt2->execute([$orgId, $year]);
$plans2 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
foreach ($plans2 as $p) echo " - $p\n";

echo "\n3. Which one matches 'แผนงานบุคลากรภาครัฐ' only?\n";
if (count($plans1) == 1 && $plans1[0] == 'แผนงานบุคลากรภาครัฐ') echo "MATCH: budget_line_items\n";
if (count($plans2) == 1 && $plans2[0] == 'แผนงานบุคลากรภาครัฐ') echo "MATCH: budget_allocations\n";

<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$db = \App\Core\Database::getInstance();

$orgId = 3;
$year = 2569;

echo "Deep Data Inspection for Org ID: $orgId, Year: $year\n";
echo "=================================================\n\n";

$sql = "SELECT bli.plan_id, pl.name_th as plan_name,
               SUM(allocated_pba) as pba,
               SUM(allocated_received) as received,
               SUM(disbursed) as disbursed,
               COUNT(*) as row_count
        FROM budget_line_items bli
        JOIN plans pl ON bli.plan_id = pl.id
        WHERE bli.division_id = ? AND bli.fiscal_year = ?
        GROUP BY bli.plan_id, pl.name_th";

$stmt = $db->prepare($sql);
$stmt->execute([$orgId, $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo sprintf("%-5s | %-40s | %-12s | %-12s | %-12s | %-5s\n", "ID", "Plan Name", "PBA", "Received", "Disbursed", "Rows");
echo str_repeat("-", 100) . "\n";

foreach ($rows as $row) {
    echo sprintf("%-5d | %-40.40s | %12.2f | %12.2f | %12.2f | %5d\n",
        $row['plan_id'], $row['plan_name'], $row['pba'], $row['received'], $row['disbursed'], $row['row_count']);
}

echo "\nChecking budget_allocations table schema...\n";
$stmtMap = $db->query("DESCRIBE budget_allocations");
print_r($stmtMap->fetchAll(PDO::FETCH_ASSOC));

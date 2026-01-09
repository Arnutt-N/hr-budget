<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$db = \App\Core\Database::getInstance();

$orgId = 3;
$year = 2569;

echo "Budget Check for Org ID: $orgId, Year: $year\n";
echo "==========================================\n\n";

echo "Summarizing budget_line_items by Plan:\n";
$sql = "SELECT pl.id, pl.name_th, 
               SUM(allocated_received) as total_alloc,
               COUNT(DISTINCT activity_id) as act_count
        FROM budget_line_items bli
        JOIN plans pl ON bli.plan_id = pl.id
        WHERE bli.division_id = ? AND bli.fiscal_year = ?
        GROUP BY pl.id, pl.name_th";

$stmt = $db->prepare($sql);
$stmt->execute([$orgId, $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No data found in budget_line_items.\n";
} else {
    foreach ($rows as $row) {
        echo sprintf("[%d] %-40s | Alloc: %12.2f | Activities: %d\n", 
            $row['id'], $row['name_th'], $row['total_alloc'], $row['act_count']);
    }
}

echo "\nChecking if budget_allocations has ANY data for this year:\n";
$sqlAny = "SELECT COUNT(*) FROM budget_allocations WHERE fiscal_year = ?";
$stmtAny = $db->prepare($sqlAny);
$stmtAny->execute([$year]);
echo "Total rows in budget_allocations for $year: " . $stmtAny->fetchColumn() . "\n";

echo "\nTop 5 rows in budget_allocations (checking column names/data):\n";
$sqlTop = "SELECT * FROM budget_allocations WHERE fiscal_year = ? LIMIT 5";
$stmtTop = $db->prepare($sqlTop);
$stmtTop->execute([$year]);
$tops = $stmtTop->fetchAll(PDO::FETCH_ASSOC);
print_r($tops);

<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$p = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

$orgId = 3;
$year = 2569;

echo "Tracing budget_line_items for division_id=$orgId...\n";

$sql = "SELECT DISTINCT bli.activity_id, a.name_th as act_name, pl.id as plan_id, pl.name_th as plan_name
        FROM budget_line_items bli
        JOIN activities a ON bli.activity_id = a.id
        JOIN projects pj ON a.project_id = pj.id
        JOIN plans pl ON pj.plan_id = pl.id
        WHERE bli.division_id = ? AND bli.fiscal_year = ?";

$stmt = $p->prepare($sql);
$stmt->execute([$orgId, $year]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "No results found in budget_line_items for this division.\n";
} else {
    foreach ($results as $row) {
        echo "Plan [{$row['plan_id']}] {$row['plan_name']} -> Activity [{$row['activity_id']}] {$row['act_name']}\n";
    }
}

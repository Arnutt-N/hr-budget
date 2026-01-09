<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$p = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

$ids = [31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45];
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "SELECT a.id, a.name_th as activity_name, pl.name_th as plan_name, pl.id as plan_id
        FROM activities a
        LEFT JOIN projects pj ON a.project_id = pj.id
        LEFT JOIN plans pl ON pj.plan_id = pl.id
        WHERE a.id IN ($placeholders)";

$stmt = $p->prepare($sql);
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Data Investigation Result:\n";
foreach ($rows as $row) {
    echo "Activity [{$row['id']}] {$row['activity_name']} -> Plan [{$row['plan_id']}] {$row['plan_name']}\n";
}

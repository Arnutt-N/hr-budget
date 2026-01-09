<?php
$config = include 'config/database.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
$tables = ['budget_allocations', 'budget_line_items', 'plans', 'projects', 'activities'];
$res = [];
foreach ($tables as $t) {
    try {
        $q = $pdo->query("DESCRIBE $t");
        $res[$t] = $q->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $res[$t] = "Error: " . $e->getMessage();
    }
}
echo json_encode($res, JSON_PRETTY_PRINT);

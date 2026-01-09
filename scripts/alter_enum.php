<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Altering Table</h1>";
try {
    $sql = "ALTER TABLE budget_plans MODIFY COLUMN plan_type ENUM('strategic','roadmap','program','project','output','activity','sub_activity') NOT NULL DEFAULT 'program'";
    $pdo->exec($sql);
    echo "<h2>Success: ALTER executed.</h2>";
} catch (Exception $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
}

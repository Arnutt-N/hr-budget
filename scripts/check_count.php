<?php
try {
    $dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "");
    $count = $pdo->query("SELECT COUNT(*) FROM budget_line_items")->fetchColumn();
    file_put_contents('count_result.txt', "Rows: " . $count);
} catch (Exception $e) {
    file_put_contents('count_result.txt', "Error: " . $e->getMessage());
}

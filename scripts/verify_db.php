<?php
require_once __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    
    // Check budget_plans
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    $exists = $stmt->rowCount() > 0;
    echo "budget_plans: " . ($exists ? "EXISTS" : "MISSING") . "\n";
    
    // Check backup
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans_backup_20260102'");
    $backup = $stmt->rowCount() > 0;
    echo "backup: " . ($backup ? "EXISTS" : "MISSING") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

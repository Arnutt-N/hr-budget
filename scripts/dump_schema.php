<?php
/**
 * Quick script to dump actual table schemas
 */
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_DATABASE'] ?? 'hr_budget'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== budget_allocations SCHEMA ===\n";
    $stmt = $pdo->query("DESCRIBE budget_allocations");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("  %-25s %s %s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NOT NULL' : '');
    }
    
    echo "\n=== plans SCHEMA ===\n";
    $stmt = $pdo->query("DESCRIBE plans");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("  %-25s %s %s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NOT NULL' : '');
    }
    
    echo "\n=== organizations SCHEMA ===\n";
    $stmt = $pdo->query("DESCRIBE organizations");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("  %-25s %s %s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NOT NULL' : '');
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

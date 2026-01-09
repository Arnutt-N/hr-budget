<?php
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
    
    echo "=== Organizations (Level 1 or No Parent) ===\n";
    $stmt = $pdo->query("SELECT id, name_th, org_type, level, parent_id FROM organizations WHERE level = 1 OR parent_id IS NULL ORDER BY id");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("ID: %d | %s | type: %s | level: %s | parent: %s\n",
            $row['id'],
            $row['name_th'],
            $row['org_type'] ?? 'NULL',
            $row['level'] ?? 'NULL',
            $row['parent_id'] ?? 'NULL'
        );
    }
    
    echo "\n=== All Organizations ===\n";
    $stmt = $pdo->query("SELECT id, name_th, org_type, level, parent_id FROM organizations ORDER BY level, id LIMIT 20");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("ID: %d | L%d | %s | type: %s | parent: %s\n",
            $row['id'],
            $row['level'] ?? 0,
            $row['name_th'],
            $row['org_type'] ?? 'NULL',
            $row['parent_id'] ?? 'NULL'
        );
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

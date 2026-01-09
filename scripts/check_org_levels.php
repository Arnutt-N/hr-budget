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
    
    echo "=== All Organizations with Hierarchy ===\n";
    $stmt = $pdo->query("
        SELECT id, name_th, org_type, level, parent_id 
        FROM organizations 
        ORDER BY COALESCE(parent_id, 0), level, id
    ");
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $indent = str_repeat('  ', $row['level'] ?? 0);
        echo sprintf("%sID:%d | L%d | %s | type:%s | parent:%s\n",
            $indent,
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

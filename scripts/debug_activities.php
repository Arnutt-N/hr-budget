<?php
/**
 * Debug script to trace activities filtering data flow
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
    
    // Get session 15
    $sessionId = 15;
    echo "=== SESSION $sessionId ===\n";
    $stmt = $pdo->prepare("SELECT * FROM disbursement_sessions WHERE id = ?");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($session);
    
    $orgId = $session['organization_id'] ?? 0;
    $fiscalYear = $session['fiscal_year'] ?? 2569;
    
    echo "\n=== Organization ID: $orgId ===\n";
    $stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
    $stmt->execute([$orgId]);
    $org = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Org Name: " . ($org['name_th'] ?? 'NOT FOUND') . "\n";
    
    echo "\n=== budget_line_items for division_id=$orgId, fiscal_year=$fiscalYear ===\n";
    $stmt = $pdo->prepare("
        SELECT DISTINCT bli.activity_id, a.name_th as activity_name, 
               p.name_th as project_name, pl.name_th as plan_name
        FROM budget_line_items bli
        LEFT JOIN activities a ON bli.activity_id = a.id
        LEFT JOIN projects p ON a.project_id = p.id
        LEFT JOIN plans pl ON p.plan_id = pl.id
        WHERE bli.division_id = ? AND bli.fiscal_year = ?
    ");
    $stmt->execute([$orgId, $fiscalYear]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($items) . " records:\n";
    foreach ($items as $item) {
        echo "  - Activity: " . ($item['activity_name'] ?? 'NULL') . 
             " | Project: " . ($item['project_name'] ?? 'NULL') .
             " | Plan: " . ($item['plan_name'] ?? 'NULL') . "\n";
    }
    
    echo "\n=== Check if budget_line_items has any data at all ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM budget_line_items");
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total budget_line_items: " . $cnt['cnt'] . "\n";
    
    echo "\n=== Check budget_line_items sample (first 5) ===\n";
    $stmt = $pdo->query("SELECT id, division_id, activity_id, fiscal_year FROM budget_line_items LIMIT 5");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        print_r($row);
    }
    
    echo "\n=== All distinct division_ids in budget_line_items ===\n";
    $stmt = $pdo->query("SELECT DISTINCT division_id, COUNT(*) as cnt FROM budget_line_items GROUP BY division_id");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "  division_id: " . ($row['division_id'] ?? 'NULL') . " => " . $row['cnt'] . " records\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

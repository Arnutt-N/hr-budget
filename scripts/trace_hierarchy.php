<?php
/**
 * Check hierarchy for specific activities
 */
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_DATABASE'] ?? 'hr_budget'
);

$activityIds = [31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45];

try {
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '');
    
    echo "Activity ID | Activity Name | Project ID | Project Name | Plan ID | Plan Name\n";
    echo "--------------------------------------------------------------------------------\n";
    
    foreach ($activityIds as $id) {
        $stmt = $pdo->prepare("
            SELECT a.id as act_id, a.name_th as act_name, 
                   pj.id as proj_id, pj.name_th as proj_name,
                   pl.id as plan_id, pl.name_th as plan_name
            FROM activities a
            LEFT JOIN projects pj ON a.project_id = pj.id
            LEFT JOIN plans pl ON pj.plan_id = pl.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            echo sprintf("%-11d | %-20.20s | %-10d | %-20.20s | %-7d | %s\n",
                $row['act_id'], $row['act_name'], 
                $row['proj_id'], $row['proj_name'],
                $row['plan_id'], $row['plan_name']
            );
        } else {
            echo "$id | NOT FOUND\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

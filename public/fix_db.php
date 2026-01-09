<?php
// fix_db.php - Temporary script to run migrations from browser
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "<h1>Database Fixer v2</h1>";

$migrations = [
    'database/migrations/021_create_budget_plans.sql',
    'database/seeds/020_seed_organizations_hierarchy.sql'
];

echo "<pre>";
foreach ($migrations as $file) {
    echo "Processing $file... ";
    $path = __DIR__ . '/../' . $file;
    
    if (!file_exists($path)) {
        echo "FAILED: File not found\n";
        continue;
    }
    
    $sql = file_get_contents($path);
    
    try {
        $pdo = Database::getInstance();
        $pdo->exec($sql);
        echo "SUCCESS\n";
    } catch (Throwable $e) {
         echo "ERROR: " . $e->getMessage() . "\n";
    }
}
echo "</pre>";
echo "<h2>Done.</h2>";

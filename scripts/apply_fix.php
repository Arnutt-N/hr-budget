<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "Starting Fix Migration...\n";

$migrations = [
    'database/migrations/019_create_budget_allocations.sql',
    'database/seeds/020_seed_organizations_hierarchy.sql'
];

foreach ($migrations as $file) {
    $path = __DIR__ . '/../' . $file;
    if (!file_exists($path)) {
        echo "Error: File not found: $file\n";
        continue;
    }

    echo "Running $file...\n";
    $sql = file_get_contents($path);
    
    try {
        $pdo = Database::getInstance();
        $pdo->exec($sql);
        echo "Success: $file\n";
    } catch (PDOException $e) {
        echo "Error running $file: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Fix Applied Successfully!\n";

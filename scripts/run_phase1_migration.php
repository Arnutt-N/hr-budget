<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

// Load .env if exists (though Database class might handle it via config/database.php)
// Ideally we should use Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load(); if it's used.
// But checking `scripts/migrate.php` it just requires vendor/autoload.php.
// Let's assume the environment is set or config works.

echo "Starting Phase 1 Migration...\n";

$migrations = [
    'database/migrations/017_drop_dimensional_tables.sql'
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
        file_put_contents(__DIR__ . '/../migration_phase1_log.txt', "Success: $file\n", FILE_APPEND);
    } catch (PDOException $e) {
        echo "Error running $file: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Phase 1 Migration Completed Successfully!\n";

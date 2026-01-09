<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "Starting Database Migration...\n";

$migrations = [
    'database/migrations/014_create_organizations.sql',
    'database/migrations/015_create_targets.sql',
    'database/migrations/016_modify_schema.sql',
    'database/seeds/001_seed_organizations.sql',
    'database/seeds/002_seed_categories.sql',
    'database/seeds/003_seed_sample_targets.sql'
];

foreach ($migrations as $file) {
    $path = __DIR__ . '/../' . $file;
    if (!file_exists($path)) {
        echo "Error: File not found: $file\n";
        continue;
    }

    echo "Running $file...\n";
    $sql = file_get_contents($path);
    
    // Split by semicolon to handle multiple statements if PDO doesn't like them in one go (though usually it handles it, but sometimes better to split)
    // However, some statements like CREATE TRIGGER/PROCEDURE might have issues with simple splitting.
    // Given the SQLs are simple CREATE/INSERT/ALTER, running directly or naive split is usually okay.
    // Database::query runs execute(), which might support multiple queries depending on driver options.
    // Safest for simple scripts: try executing the whole block. If it fails, maybe split.
    
    try {
        // We use the raw PDO instance for flexibility
        $pdo = Database::getInstance();
        $pdo->exec($sql);
        echo "Success: $file\n";
    } catch (PDOException $e) {
        echo "Error running $file: " . $e->getMessage() . "\n";
        // Continue or stop? Let's stop on error to be safe.
        // But for "IF NOT EXISTS", it shouldn't error.
        // We'll stop on error.
        exit(1);
    }
}

echo "Migration Completed Successfully!\n";

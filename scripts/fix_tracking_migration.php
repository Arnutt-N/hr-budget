<?php
require_once __DIR__ . '/src/Core/Database.php';

use App\Core\Database;

// Initialize Database connection
$config = require __DIR__ . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

echo "Running migration: 025_restore_category_id_to_items.sql...\n";

try {
    $sql = file_get_contents(__DIR__ . '/database/migrations/025_restore_category_id_to_items.sql');
    $pdo->exec($sql);
    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column already exists. Skipping.\n";
    } else {
        echo "Error running migration: " . $e->getMessage() . "\n";
    }
}

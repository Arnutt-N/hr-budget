<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getPdo();

echo "=== Tables in hr_budget database ===\n\n";

try {
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found!\n";
    } else {
        echo "Found " . count($tables) . " tables:\n\n";
        foreach ($tables as $i => $table) {
            echo ($i + 1) . ". " . $table . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

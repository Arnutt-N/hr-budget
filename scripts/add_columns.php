<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Adding organization_id to budget_trackings ===\n";

$pdo = Database::getInstance();

// Add organization_id column to budget_trackings
$sql = "ALTER TABLE budget_trackings ADD COLUMN organization_id INT DEFAULT NULL AFTER fiscal_year";

echo "Running: $sql\n";
try {
    $pdo->exec($sql);
    echo "OK!\n";
} catch (Exception $e) {
    // Might already exist
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column already exists, skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Also add is_plan and plan_name to budget_categories if not exists
$sql2 = "ALTER TABLE budget_categories 
    ADD COLUMN is_plan BOOLEAN DEFAULT FALSE,
    ADD COLUMN plan_name VARCHAR(255) DEFAULT NULL";

echo "Adding is_plan and plan_name to budget_categories...\n";
try {
    $pdo->exec($sql2);
    echo "OK!\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Columns already exist, skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "=== Done ===\n";

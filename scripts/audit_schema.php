<?php
// scripts/audit_schema.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

// Helper to get table details
function getTableSchema($table) {
    try {
        $db = Database::getInstance();
        $cols = $db->query("SHOW FULL COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        $keys = $db->query("SHOW KEYS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        return ['columns' => $cols, 'keys' => $keys];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// List of expected tables
$tables = [
    'budget_years',
    'budget_categories',
    'budget_category_items',
    'budget_requests',
    'budget_request_items',
    'budget_approvals', // or whatever it was called
    'budget_trackings', // The one user mentioned missing before
    'budget_results'    // Renamed one?
];

// Get all actual tables
$actualTables = Database::query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "--- DATABASE AUDIT ---\n";
echo "Found Tables: " . implode(", ", $actualTables) . "\n\n";

foreach ($actualTables as $table) {
    echo "TABLE: $table\n";
    $schema = getTableSchema($table);
    if (isset($schema['error'])) {
        echo "  Error: " . $schema['error'] . "\n";
        continue;
    }
    
    echo "  COLUMNS:\n";
    foreach ($schema['columns'] as $col) {
        echo sprintf("    - %-20s %-15s %s\n", $col['Field'], $col['Type'], $col['Key'] ? "[KEY: {$col['Key']}]" : "");
    }
    
    echo "  KEYS/INDEXES:\n";
    foreach ($schema['keys'] as $key) {
        echo sprintf("    - %-15s %s (%s)\n", $key['Key_name'], $key['Column_name'], $key['Non_unique'] ? 'Non-Unique' : 'Unique');
    }
    echo "\n";
}

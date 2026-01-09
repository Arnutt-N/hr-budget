<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/src/Core/Database.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = \App\Core\Database::getInstance();
    
    echo "=== expense_items columns ===\n";
    $stmt = $db->query("SHOW COLUMNS FROM expense_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " | " . $col['Type'] . "\n";
    }
    
    echo "\n=== Checking for expense_type_id ===\n";
    $found = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'expense_type_id') {
            $found = true;
            break;
        }
    }
    echo $found ? "FOUND" : "MISSING";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

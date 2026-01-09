<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/src/Core/Database.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = \App\Core\Database::getInstance();
    
    echo "Checking for record_month column in budget_trackings...\n";
    $stmt = $db->query("SHOW COLUMNS FROM budget_trackings LIKE 'record_month'");
    $col = $stmt->fetch();
    
    if (!$col) {
        echo "Column MISSING. Adding it now...\n";
        $sql = "ALTER TABLE budget_trackings ADD COLUMN record_month INT NULL AFTER fiscal_year";
        $db->query($sql);
        echo "Column record_month added successfully.\n";
        
        // Add index
        $db->query("CREATE INDEX idx_trackings_month ON budget_trackings(record_month)");
        echo "Index added.\n";
    } else {
        echo "Column ALREADY EXISTS.\n";
    }
    
    echo "\nDONE!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

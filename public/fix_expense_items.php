<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/src/Core/Database.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = \App\Core\Database::getInstance();
    
    echo "Adding expense_type_id column to expense_items...\n";
    
    // Add the missing column
    $sql = "ALTER TABLE expense_items ADD COLUMN expense_type_id INT NULL AFTER expense_group_id";
    $db->query($sql);
    echo "Column added.\n";
    
    // Add foreign key
    $sql2 = "ALTER TABLE expense_items ADD CONSTRAINT fk_items_expense_type 
             FOREIGN KEY (expense_type_id) REFERENCES expense_types(id) ON DELETE SET NULL";
    try {
        $db->query($sql2);
        echo "Foreign key added.\n";
    } catch (Exception $e) {
        echo "FK Warning: " . $e->getMessage() . "\n";
    }
    
    // Now populate expense_type_id from expense_groups
    echo "Populating expense_type_id from expense_groups...\n";
    $sql3 = "UPDATE expense_items ei 
             JOIN expense_groups eg ON ei.expense_group_id = eg.id 
             SET ei.expense_type_id = eg.expense_type_id";
    $db->query($sql3);
    echo "Data populated.\n";
    
    echo "\nDONE!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

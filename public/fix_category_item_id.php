<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/src/Core/Database.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = \App\Core\Database::getInstance();
    
    echo "Making budget_category_item_id nullable...\n";
    $sql = "ALTER TABLE budget_trackings MODIFY COLUMN budget_category_item_id INT NULL";
    $db->query($sql);
    echo "Column modified to allow NULL.\n";
    echo "\nDONE!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

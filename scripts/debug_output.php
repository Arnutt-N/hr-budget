<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$output = "";
try {
    $db = Database::getPdo();
    $id = 5;
    
    // Wipe items
    $countBefore = $db->query("SELECT count(*) FROM budget_request_items WHERE budget_request_id = $id")->fetchColumn();
    $db->exec("DELETE FROM budget_request_items WHERE budget_request_id = $id");
    $countAfter = $db->query("SELECT count(*) FROM budget_request_items WHERE budget_request_id = $id")->fetchColumn();
    
    $output .= "Items before: $countBefore\nItems after: $countAfter\n";
    
    // Check master items
    $masterCount = $db->query("SELECT count(*) FROM budget_category_items")->fetchColumn();
    $output .= "Master items available: $masterCount\n";
    
} catch (Exception $e) {
    $output .= "Error: " . $e->getMessage();
}

file_put_contents('debug_result.txt', $output);

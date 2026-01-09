<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $db = Database::getInstance();
    
    $searchTerm = "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ";
    
    echo "Searching for Expense Groups matching: $searchTerm\n";
    
    // Search in groups
    $sql = "SELECT * FROM expense_groups WHERE name_th LIKE ?";
    $groups = Database::query($sql, ["%{$searchTerm}%"]);
    
    if (empty($groups)) {
        echo "No groups found directly matching.\n";
    } else {
        foreach ($groups as $group) {
            echo "Found Group: [{$group['id']}] {$group['name_th']} (Type ID: {$group['expense_type_id']})\n";
            
            // List items
            $sqlItems = "SELECT * FROM expense_items WHERE expense_group_id = ?";
            $items = Database::query($sqlItems, [$group['id']]);
            
            echo "  Items:\n";
            foreach ($items as $item) {
                echo "  - [{$item['id']}] {$item['name_th']}\n";
            }
        }
    }
    
    // Also check partial
    $partial = "ค่าตอบแทนรายเดือน";
    echo "\nChecking partial matches for '$partial'...\n";
    $sql = "SELECT * FROM expense_groups WHERE name_th LIKE ?";
    $groups = Database::query($sql, ["%{$partial}%"]);
    
    foreach ($groups as $group) {
         echo "Partial Group Match: [{$group['id']}] {$group['name_th']} (Type ID: {$group['expense_type_id']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
// C:\laragon\www\hr_budget\public\inspect_expenses.php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $sql = "SELECT i.id, i.name_th, i.parent_id, i.expense_group_id, g.name_th as group_name 
            FROM expense_items i
            JOIN expense_groups g ON i.expense_group_id = g.id
            WHERE i.name_th LIKE '%ใช้สอย%' OR g.name_th LIKE '%ใช้สอย%'
            ORDER BY g.id, i.sort_order";
    
    $items = Database::query($sql, []);
    
    header('Content-Type: application/json');
    echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

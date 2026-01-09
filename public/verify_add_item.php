<?php
// C:\laragon\www\hr_budget\public\verify_add_item.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getInstance();
$name = 'เงินสมทบกองทุนเงินทดแทน';

echo "Searching for '$name'...\n";

$item = Database::queryOne("SELECT * FROM expense_items WHERE name_th = ?", [$name]);

if ($item) {
    echo "FOUND Item ID: {$item['id']}\n";
    echo "Parent ID: {$item['parent_id']}\n";
    echo "Sort Order: {$item['sort_order']}\n";
    
    // Check line item
    $line = Database::queryOne("SELECT * FROM budget_line_items WHERE expense_item_id = ? AND division_id = 3", [$item['id']]);
    if ($line) {
        echo "FOUND Budget Line Item for Org 3 (ID: {$line['id']})\n";
    } else {
        echo "MISSING Budget Line Item for Org 3\n";
    }
} else {
    echo "Item NOT FOUND\n";
}

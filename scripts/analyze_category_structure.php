<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

// Check budget_category_items structure
echo "Analying budget_category_items...\n";
$items = Database::query("SELECT * FROM budget_category_items ORDER BY item_level, sort_order");

$levels = [];
foreach ($items as $item) {
    echo str_repeat("  ", $item['item_level']) . "- " . $item['name'] . " (ID: {$item['id']}, Parent: {$item['parent_id']})\n";
    $levels[$item['item_level']] = ($levels[$item['item_level']] ?? 0) + 1;
}

echo "\nLevel Distribution:\n";
print_r($levels);

// Check existing budget_request_items
echo "\nChecking existing budget_request_items...\n";
$requestItems = Database::query("SELECT * FROM budget_request_items");
echo "Count: " . count($requestItems) . "\n";
foreach ($requestItems as $ri) {
    echo "Request Item ID: {$ri['id']}, Category Item ID: {$ri['category_item_id']}\n";
}

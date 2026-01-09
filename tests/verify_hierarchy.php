<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Model.php';
require_once __DIR__ . '/../src/Core/SimpleQueryBuilder.php';
require_once __DIR__ . '/../src/Models/ExpenseGroup.php';

use App\Models\ExpenseGroup;

// Mock Database Connection (uses existing db_config via Database class)
// Note: This relies on Database class working in CLI environment

echo "Verifying Hierarchy for Expense Type 1 (งบบุคลากร)...\n";

try {
    $groups = ExpenseGroup::getAllWithHierarchicalItemsByType(1);
    
    foreach ($groups as $group) {
        echo "\n[Group] " . $group['name_th'] . "\n";
        printTree($group['items']);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function printTree($items, $level = 0) {
    foreach ($items as $item) {
        echo str_repeat("  ", $level) . "- " . $item['name_th'] . " (ID: " . $item['id'] . ", Parent: " . $item['parent_id'] . ")\n";
        if (!empty($item['children'])) {
            printTree($item['children'], $level + 1);
        }
    }
}

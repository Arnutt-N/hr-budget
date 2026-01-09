<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Models\ExpenseGroup;

// Bootstrap
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$typeId = 1; // งบบุคลากร
$organizationId = 3; // From conversation history, user was working on Org 3
echo "Dumping Expense Tree for Type: $typeId, Org: $organizationId\n";

$groups = ExpenseGroup::getAllWithItemsByType($typeId, $organizationId);

function printItem($item, $indent = "") {
    echo "{$indent}[{$item['id']}] {$item['name_th']} (Parent: " . ($item['parent_id'] ?? 'NULL') . ", children: " . (isset($item['children']) ? count($item['children']) : 0) . ")\n";
    if (!empty($item['children'])) {
        foreach ($item['children'] as $child) {
            printItem($child, $indent . "  ");
        }
    }
}

foreach ($groups as $group) {
    echo "Group: [{$group['id']}] {$group['name_th']}\n";
    foreach ($group['items'] as $item) {
        printItem($item, "  ");
    }
}

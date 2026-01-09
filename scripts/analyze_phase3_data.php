<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

// 1. Check budget_category_items hierarchy
echo "=== 1. CATEGORY HIERARCHY ===\n";
try {
    $sql = "SELECT id, name, parent_id, item_level, sort_order 
            FROM budget_category_items 
            ORDER BY parent_id, sort_order";
    $items = Database::query($sql);
    
    if (empty($items)) {
        echo "No items found in budget_category_items.\n";
    } else {
        // Build simple tree for display
        $lookup = [];
        foreach ($items as $item) {
            $lookup[$item['id']] = $item;
            $lookup[$item['id']]['children'] = [];
        }
        
        $tree = [];
        foreach ($lookup as $id => &$item) {
            if ($item['parent_id'] && isset($lookup[$item['parent_id']])) {
                $lookup[$item['parent_id']]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }
        
        // Recursive display function
        function displayTree($nodes, $depth = 0) {
            foreach ($nodes as $node) {
                echo str_repeat("  ", $depth) . "- [L{$node['item_level']}] {$node['name']} (ID: {$node['id']})\n";
                if (!empty($node['children'])) {
                    displayTree($node['children'], $depth + 1);
                }
            }
        }
        
        displayTree($tree);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 2. Check existing dimensional structure
echo "\n=== 2. EXISTING DIMENSIONS (Top 20) ===\n";
try {
    $dims = Database::query("SELECT * FROM dim_budget_structure LIMIT 20");
    foreach ($dims as $d) {
        echo "[ID: {$d['structure_id']}] Plan: {$d['plan_name']} | Output: {$d['output_name']} | Activity: {$d['activity_name']} | Item: {$d['item_name']}\n";
    }
} catch (Exception $e) {
    echo "Error checking dimensions: " . $e->getMessage() . "\n";
}

// 3. Check Request Items count
echo "\n=== 3. REQUEST ITEMS ===\n";
try {
    $count = Database::queryOne("SELECT COUNT(*) as c FROM budget_request_items");
    echo "Total items: " . $count['c'] . "\n";
} catch (Exception $e) {
    echo "Error checking request items: " . $e->getMessage() . "\n";
}

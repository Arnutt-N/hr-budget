<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Database Setup Verification ===\n\n";

$db = Database::getPdo();

// Check if table exists
echo "1. Checking budget_category_items table...\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'budget_category_items'");
    $tableExists = $stmt->rowCount() > 0;
    echo $tableExists ? "   ✓ Table EXISTS\n" : "   ✗ Table DOES NOT EXIST\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Check if column exists
echo "\n2. Checking category_item_id column in budget_request_items...\n";
try {
    $stmt = $db->query("SHOW COLUMNS FROM budget_request_items LIKE 'category_item_id'");
    $columnExists = $stmt->rowCount() > 0;
    echo $columnExists ? "   ✓ Column EXISTS\n" : "   ✗ Column DOES NOT EXIST\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Check for data
echo "\n3. Checking personnel budget items...\n";
try {
    // Find personnel category
    $stmt = $db->query("SELECT id, name_th FROM budget_categories WHERE name_th LIKE '%บุคลากร%' LIMIT 1");
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cat) {
        echo "   ✓ Found category: {$cat['name_th']} (ID: {$cat['id']})\n";
        
        // Count items
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM budget_category_items WHERE category_id = ?");
        $stmt->execute([$cat['id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        
        echo "   ✓ Found {$count} items in this category\n";
        
        if ($count > 0) {
            echo "\n   Items:\n";
            $stmt = $db->prepare("SELECT item_name, default_unit, requires_quantity FROM budget_category_items WHERE category_id = ? ORDER BY sort_order");
            $stmt->execute([$cat['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($items as $item) {
                $reqQty = $item['requires_quantity'] ? 'YES' : 'NO';
                echo "   - {$item['item_name']} ({$item['default_unit']}) [Req Qty: {$reqQty}]\n";
            }
        }
    } else {
        echo "   ✗ Personnel category NOT FOUND\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Verification Complete ===\n";

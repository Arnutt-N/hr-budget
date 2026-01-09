<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getPdo();

echo "=== Budget Category Items Verification ===\n\n";

// Count
$cnt = $db->query('SELECT COUNT(*) FROM budget_category_items')->fetchColumn();
$fields = $db->query('SELECT COUNT(*) FROM budget_category_items WHERE is_header=0')->fetchColumn();

echo "Total items: $cnt\n";
echo "Editable fields: $fields\n\n";

// List by category
$cats = $db->query('SELECT id, name_th FROM budget_categories ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

foreach ($cats as $cat) {
    echo "--- {$cat['name_th']} (Category ID: {$cat['id']}) ---\n";
    
    $items = $db->prepare('SELECT item_name, is_header, level FROM budget_category_items WHERE category_id = ? ORDER BY sort_order');
    $items->execute([$cat['id']]);
    
    while ($item = $items->fetch(PDO::FETCH_ASSOC)) {
        $indent = str_repeat('  ', $item['level']);
        $type = $item['is_header'] ? '[H]' : '[F]';
        echo "$indent$type {$item['item_name']}\n";
    }
    echo "\n";
}

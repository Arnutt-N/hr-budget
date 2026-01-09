<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$id = 5;
$db = Database::getPdo();

// 1. Check existing items
$sql = "SELECT * FROM budget_request_items WHERE budget_request_id = $id";
$items = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "Request #$id has " . count($items) . " existing items.\n";
foreach ($items as $item) {
    echo " - Item ID: {$item['id']}, CatItem: {$item['category_item_id']}, Name: {$item['item_name']}\n";
}

// 2. Check budget_category_items
$catItems = $db->query("SELECT * FROM budget_category_items LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "\nTop 5 Master Items:\n";
foreach ($catItems as $c) {
    echo " - [{$c['item_code']}] {$c['item_name']} (Lvl: {$c['level']})\n";
}

// 3. WIPE Request items to force clean reload
if (count($items) > 0) {
    echo "\nDeleting old items for Request #$id...\n";
    $db->exec("DELETE FROM budget_request_items WHERE budget_request_id = $id");
    echo "Deleted.\n";
}

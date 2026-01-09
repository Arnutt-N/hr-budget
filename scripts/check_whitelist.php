<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

// Bootstrap
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$db = Database::getInstance();
$record = Database::queryOne("SELECT * FROM budget_trackings WHERE id = 1");
$orgId = $record['organization_id'] ?? 3;

echo "Session 1 Org: $orgId\n";

// Dump Items for this org and type 1
$sql = "SELECT i.* FROM expense_items i 
        JOIN budget_line_items l ON i.id = l.expense_item_id 
        WHERE l.division_id = ? AND l.expense_type_id = 1";
$whitelisted = Database::query($sql, [$orgId]);

echo "Whitelisted Items for Org $orgId:\n";
foreach ($whitelisted as $w) {
    echo " - [{$w['id']}] {$w['name_th']}\n";
}

// Check if Parent ID 8 is whitelisted or if children are whitelisted but parent isn't
echo "\nChecking Items 8, 9, 10:\n";
$ids = [8, 9, 10];
foreach ($ids as $id) {
    $item = Database::queryOne("SELECT * FROM expense_items WHERE id = ?", [$id]);
    if ($item) {
        echo "Item [{$item['id']}]: {$item['name_th']} | Parent: {$item['parent_id']} | is_header: {$item['is_header']}\n";
        // Check if whitelisted
        $wl = Database::queryOne("SELECT 1 FROM budget_line_items WHERE division_id = ? AND expense_item_id = ?", [$orgId, $id]);
        echo "   -> Whitelisted: " . ($wl ? "YES" : "NO") . "\n";
    } else {
        echo "Item [{$id}]: NOT FOUND\n";
    }
}

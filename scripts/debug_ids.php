<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

// Bootstrap
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 1. Find the "Parent Item" which is supposedly an existing item
$parentName = "%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช)%";
echo "Searching for Parent Item like: $parentName\n";

$parentItem = Database::queryOne("SELECT * FROM expense_items WHERE name_th LIKE ?", [$parentName]);

if ($parentItem) {
    echo "Found Parent Item: [{$parentItem['id']}] {$parentItem['name_th']} (Group: {$parentItem['expense_group_id']})\n";
    
    // Check if this parent acts as a header
    echo "Current is_header: {$parentItem['is_header']}\n";
    
    // 2. Find the "New Group" I created by mistake
    $groupName = "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)";
    $newGroup = Database::queryOne("SELECT * FROM expense_groups WHERE name_th = ?", [$groupName]);
    
    if ($newGroup) {
        echo "Found Mistaken Group: [{$newGroup['id']}] {$newGroup['name_th']}\n";
        
        // 3. Find items in this mistaken group
        $items = Database::query("SELECT * FROM expense_items WHERE expense_group_id = ?", [$newGroup['id']]);
        echo "Items in mistaken group:\n";
        foreach ($items as $item) {
            echo " - [{$item['id']}] {$item['name_th']}\n";
        }
    } else {
        echo "Mistaken Group NOT FOUND (Maybe name mismatch?)\n";
        // List recent groups?
         $recentGroups = Database::query("SELECT * FROM expense_groups ORDER BY id DESC LIMIT 5");
         echo "Recent Groups:\n";
         foreach ($recentGroups as $g) echo "[{$g['id']}] {$g['name_th']}\n";
    }

} else {
    echo "Parent Item NOT FOUND.\n";
    // Maybe search loosely
    $loose = "%วิชาชีพเฉพาะ%";
    $items = Database::query("SELECT * FROM expense_items WHERE name_th LIKE ? LIMIT 5", [$loose]);
     foreach ($items as $item) {
            echo " - Potential Match: [{$item['id']}] {$item['name_th']} (Group: {$item['expense_group_id']})\n";
    }
}

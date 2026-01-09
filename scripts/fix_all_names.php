<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getPdo();

echo "=== Fixing Database Names ===\n\n";

// 1. Fix category names - remove dash
echo "[1] Fixing category names...\n";
$stmt = $db->query("SELECT id, name_th FROM budget_categories");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $oldName = $row['name_th'];
    $newName = preg_replace('/^[—–-]\s*/', '', $oldName); // Remove leading dash
    $newName = trim($newName);
    
    if ($oldName !== $newName) {
        $update = $db->prepare("UPDATE budget_categories SET name_th = ? WHERE id = ?");
        $update->execute([$newName, $row['id']]);
        echo "    Fixed: '$oldName' -> '$newName'\n";
    }
}

// 2. Fix item name
echo "\n[2] Fixing item names...\n";
$update = $db->prepare("UPDATE budget_category_items SET item_name = ? WHERE item_name = ?");
$update->execute(['เงินเพิ่มอื่นๆ', 'เงินเพิ่มพิเศษ']);
echo "    Fixed: 'เงินเพิ่มพิเศษ' -> 'เงินเพิ่มอื่นๆ'\n";

// Also fix in root items
$stmt = $db->query("SELECT id, item_name FROM budget_category_items");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $oldName = $row['item_name'];
    $newName = preg_replace('/^[—–-]\s*/', '', $oldName);
    $newName = trim($newName);
    
    if ($oldName !== $newName) {
        $update = $db->prepare("UPDATE budget_category_items SET item_name = ? WHERE id = ?");
        $update->execute([$newName, $row['id']]);
        echo "    Fixed item: '$oldName' -> '$newName'\n";
    }
}

// 3. Verify
echo "\n[3] Verification:\n";
echo "\nCategories:\n";
$stmt = $db->query("SELECT name_th FROM budget_categories ORDER BY sort_order");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['name_th']}\n";
}

echo "\nSection Headers:\n";
$stmt = $db->query("SELECT item_name FROM budget_category_items WHERE is_header = 1 AND level = 1 ORDER BY sort_order");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['item_name']}\n";
}

echo "\n=== Done! ===\n";

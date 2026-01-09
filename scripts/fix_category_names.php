<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getPdo();

echo "Fixing category names...\n";

// Remove "— " prefix from names
$db->exec("UPDATE budget_categories SET name_th = TRIM(LEADING '— ' FROM name_th)");
$db->exec("UPDATE budget_categories SET name_th = TRIM(LEADING '—' FROM name_th)");
$db->exec("UPDATE budget_categories SET name_th = TRIM(name_th)");

// Also update in budget_category_items if needed
$db->exec("UPDATE budget_category_items SET item_name = TRIM(LEADING '— ' FROM item_name)");
$db->exec("UPDATE budget_category_items SET item_name = TRIM(LEADING '—' FROM item_name)");

echo "\nVerification:\n";
$stmt = $db->query("SELECT id, name_th FROM budget_categories ORDER BY sort_order ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['name_th']}\n";
}

echo "\nDone!\n";

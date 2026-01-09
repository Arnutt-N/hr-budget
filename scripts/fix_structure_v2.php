<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

// Bootstrap
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();
echo "Start Correction...\n";

// 1. Find Mistaken Group
$mistakenGroupName = "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)";
$mistakenGroup = Database::queryOne("SELECT * FROM expense_groups WHERE name_th = ? ORDER BY id DESC", [$mistakenGroupName]);

if (!$mistakenGroup) {
    echo "Mistaken Group NOT FOUND (Maybe already deleted or name mismatch).\n";
} else {
    echo "Found Mistaken Group: ID {$mistakenGroup['id']}\n";
}

// 2. Find New Items (created in previous step)
// If Mistaken Group found, search by GroupID. Else search by Name.
$newItems = [];
if ($mistakenGroup) {
    $newItems = Database::query("SELECT * FROM expense_items WHERE expense_group_id = ?", [$mistakenGroup['id']]);
} else {
    // Fallback: search by name
    $names = [
        "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์",
        "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก"
    ];
    foreach ($names as $n) {
        $res = Database::queryOne("SELECT * FROM expense_items WHERE name_th = ?", [$n]);
        if ($res) $newItems[] = $res;
    }
}

echo "Found " . count($newItems) . " new items to move.\n";

// 3. Find Real Parent Item
// It should be an ITEM, not a GROUP. matching the name.
$realParent = Database::queryOne("SELECT * FROM expense_items WHERE name_th LIKE ? AND id NOT IN (" . (empty($newItems) ? '0' : implode(',', array_column($newItems, 'id'))) . ") LIMIT 1", ["%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช)%"]);

if (!$realParent) {
    echo "CRITICAL: Real Parent Item NOT FOUND.\n";
    // Try wider search
    $loose = "%วิชาชีพเฉพาะ%";
    $candidates = Database::query("SELECT * FROM expense_items WHERE name_th LIKE ? LIMIT 5", [$loose]);
    echo "Candidates:\n";
    foreach ($candidates as $c) echo "- [{$c['id']}] {$c['name_th']}\n";
    exit;
}

echo "Found Real Parent Item: [{$realParent['id']}] Group: {$realParent['expense_group_id']}\n";

// 4. EXECUTE UPDATES
try {
    $db->beginTransaction();
    
    // A. Update Parent to be Header
    $db->query("UPDATE expense_items SET is_header = 1 WHERE id = ?", [$realParent['id']]);
    echo "Updated Parent (ID {$realParent['id']}) to is_header=1\n";
    
    // B. Move New Items
    if (!empty($newItems)) {
        foreach ($newItems as $item) {
            $db->query("UPDATE expense_items SET expense_group_id = ?, parent_id = ? WHERE id = ?", [
                $realParent['expense_group_id'], 
                $realParent['id'], 
                $item['id']
            ]);
            echo "Moved Item (ID {$item['id']}) to Group {$realParent['expense_group_id']} under Parent {$realParent['id']}\n";
        }
    }
    
    // C. Delete Mistaken Group
    if ($mistakenGroup) {
        $db->query("DELETE FROM expense_groups WHERE id = ?", [$mistakenGroup['id']]);
        echo "Deleted Mistaken Group (ID {$mistakenGroup['id']})\n";
    }
    
    $db->commit();
    echo "SUCCESS: Correction Complete.\n";
    
} catch (Exception $e) {
    $db->rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}

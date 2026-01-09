<?php
// public/restore_items.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

header('Content-Type: application/json');

// IDs to RESTORE (Re-activate)
// 22 = ค่าจ้างประจำ (Header)
// 29 = อัตราเดิม (Child of 28)
// 30 = อัตราใหม่ (Child of 28)
// 23, 24 = Also อัตราเดิม/ใหม่ children of ID 2 which is เงินเดือน (also deleted!)
// 2 = เงินเดือน (Header) - User said "Remove duplicates" but if they remove the header, children 3&4 are loose?
// Let's restore the Headers and Children that are structural.
// The user complained about "Duplicate", but for "Gov Employee" (28), the children ARE "Old Rate" (29) and "New Rate" (30).
// If I delete 29/30, then 28 has no children -> Empty -> Hidden by my "Hide Empty Logic"!
// So I MUST restore 29, 30.
// Also 22 (Header).
// Also 17? "เงินช่วยเหลือการครองชีพ..." - It was ID 17. Use said "Remove". But if it's the only one?
// Let's focus on: 22, 29, 30 first as confirmed issues.

$ids = [22, 29, 30]; 

$results = [];

try {
    foreach ($ids as $id) {
        $count = Database::update('budget_category_items', ['is_active' => 1], 'id = ?', [$id]);
        $results[$id] = $count > 0 ? 'Restored' : 'No Change';
    }
    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

<?php
// scripts/soft_delete_items.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

// List of exact names or patterns to delete based on user request (Short side of pairs)
$targets = [
    'เงินเดือน',
    'อัตราเดิม',
    'อัตราใหม่',
    'เงินช่วยเหลือการครองชีพข้าราชการระดับต้น',
    'ค่าจ้างประจำ', // From pair: ค่าจ้างประจำ vs ค่าจ้างประจำและค่าตอบแทนอื่นๆ
    'ค่าใช้จ่าย',   // From pair: ค่าใช้จ่าย vs ค่าใช้จ่ายต่างๆ
];

echo "Soft Deleting Items:\n";
echo "-------------------\n";

foreach ($targets as $name) {
    try {
        $sql = "UPDATE budget_category_items SET is_active = 0 WHERE item_name = ? AND is_active = 1";
        $count = Database::update('budget_category_items', ['is_active' => 0], 'item_name = ?', [$name]);
        
        if ($count > 0) {
            echo "[OK] Deactivated '$name' ($count items)\n";
        } else {
            // Try fuzzy for some? No, strictly follow exact match preference unless confirmed.
            echo "[SKIP] '$name' not found or already inactive\n";
        }
    } catch (Exception $e) {
        echo "[ERROR] '$name': " . $e->getMessage() . "\n";
    }
}

<?php
// public/restore_all_cleanup.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

header('Content-Type: application/json');

// List of Checked IDs to Restore
$ids = [
    2,  // เงินเดือน (Header)
    3, 4, // อัตราเดิม/ใหม่ (Salary Children)
    22, // ค่าจ้างประจำ (Header) - Already restored but safe to redo
    23, 24, // อัตราเดิม/ใหม่ (Wages Children)
    17, // เงินช่วยเหลือการครองชีพ...
    29, 30, // อัตราเดิม/ใหม่ (Gov Employee Children) - Already restored
];

// Handle 'ค่าใช้จ่าย' which was deleted by Name
$check = Database::query("SELECT id FROM budget_category_items WHERE item_name = 'ค่าใช้จ่าย'");
foreach ($check as $row) {
    $ids[] = $row['id'];
}

$results = [];

try {
    foreach ($ids as $id) {
        $count = Database::update('budget_category_items', ['is_active' => 1], 'id = ?', [$id]);
        $results[$id] = $count > 0 ? 'Restored' : 'Active/NoChange';
    }
    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

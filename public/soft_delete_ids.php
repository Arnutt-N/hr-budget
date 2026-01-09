<?php
// public/soft_delete_ids.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

header('Content-Type: application/json');

// IDs to deactivate based on User's "Remove" list matches
$ids = [
    2,  // เงินเดือน
    3, 23, 29, // อัตราเดิม (All copies)
    4, 24, 30, // อัตราใหม่ (All copies)
    17, // เงินช่วยเหลือการครองชีพข้าราชการระดับต้น
    22, // ค่าจ้างประจำ
];

$results = [];

try {
    foreach ($ids as $id) {
        $count = Database::update('budget_category_items', ['is_active' => 0], 'id = ?', [$id]);
        $results[$id] = $count > 0 ? 'Deactivated' : 'Failed/Already Inactive';
    }
    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

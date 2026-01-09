<?php
// public/debug_specific_items.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

header('Content-Type: application/json');

$terms = ['ค่าจ้างประจำ', 'ค่าตอบแทนพนักงานราชการ'];
$results = [];

$results = [];

// 1. Reactivate ID 22
$cnt = Database::update('budget_category_items', ['is_active' => 1], 'id = 22');
$results['reactivate_22'] = $cnt;

// 2. Check Children of 'ค่าตอบแทนพนักงานราชการ' (Header ID 28)
// Assuming children follow ID 28 immediately? Or check by category?
// It is Cat ID 1. Let's look at items around ID 28.
$sql = "SELECT id, item_name, level, is_header, is_active 
        FROM budget_category_items 
        WHERE id > 28 AND id < 40 AND category_id = 1
        ORDER BY id ASC";
$rows = Database::query($sql);
$results['children_of_28'] = $rows;

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

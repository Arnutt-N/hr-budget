<?php
// public/check_parents.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

header('Content-Type: application/json');

$ids = [3, 4, 23, 24]; // The other "Old/New Rate" items I deleted

$sql = "SELECT id, item_name, category_id, sort_order, is_active FROM budget_category_items WHERE id IN (3, 4, 23, 24)";
$rows = Database::query($sql);

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

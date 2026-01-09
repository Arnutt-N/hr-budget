<?php
// public/debug_duplicates.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

header('Content-Type: application/json');

try {
    $sql = "SELECT id, item_name, category_id, level, is_header, sort_order 
            FROM budget_category_items 
            WHERE is_active = 1 
            ORDER BY category_id, sort_order, id";
            
    $items = Database::query($sql);
    
    echo json_encode(['success' => true, 'count' => count($items), 'items' => $items], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

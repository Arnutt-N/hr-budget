<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $db = Database::getPdo();
    $count = $db->query("SELECT count(*) FROM budget_category_items")->fetchColumn();
    echo "Items Count: " . $count . "\n";
    
    $cols = $db->query("SHOW COLUMNS FROM budget_request_items LIKE 'remark'");
    echo "Remark Column: " . ($cols->rowCount() > 0 ? 'Exists' : 'Missing') . "\n";
    
    $cols2 = $db->query("SHOW COLUMNS FROM budget_category_items LIKE 'is_header'");
    echo "is_header Column: " . ($cols2->rowCount() > 0 ? 'Exists' : 'Missing') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

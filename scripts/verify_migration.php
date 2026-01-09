<?php
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/config/database.php';

use App\Core\Database;

// Initialize database connection
$config = require __DIR__ . '/config/database.php';
// Mock ENV if needed or rely on Database class handling it. 
// Assuming Database class uses internal config or env.
// Let's try to just use PDO directly to be safe and dependency-free if possible, 
// or use the App's Database class if it's reliable.
// The user's project uses App\Core\Database.

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, code, name_th, parent_id, level FROM budget_categories ORDER BY level ASC, sort_order ASC, id ASC LIMIT 20");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "--- Category Structure Check ---\n";
    echo sprintf("%-5s | %-20s | %-10s | %-6s | %s\n", "ID", "Code", "ParentID", "Level", "Name");
    echo str_repeat("-", 60) . "\n";

    foreach ($categories as $cat) {
        echo sprintf("%-5d | %-20s | %-10s | %-6d | %s\n", 
            $cat['id'], 
            substr($cat['code'], 0, 20), 
            $cat['parent_id'] ?? 'NULL', 
            $cat['level'], 
            $cat['name_th']
        );
    }
    echo "------------------------------\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

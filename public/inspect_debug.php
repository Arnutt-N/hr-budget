<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

try {
    // Check budget_category_items structure
    echo "<h3>budget_category_items Table Structure:</h3>";
    $cols = \App\Core\Database::query("SHOW COLUMNS FROM budget_category_items");
    echo "<pre>" . json_encode($cols, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // Check sample data
    echo "<h3>Sample Data (first 10):</h3>";
    $items = \App\Core\Database::query("SELECT id, name, category_id, parent_id, level FROM budget_category_items ORDER BY sort_order, id LIMIT 10");
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>category_id</th><th>parent_id</th><th>level</th></tr>";
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>{$item['id']}</td>";
        echo "<td>{$item['name']}</td>";
        echo "<td>{$item['category_id']}</td>";
        echo "<td>" . ($item['parent_id'] ?? 'NULL') . "</td>";
        echo "<td>{$item['level']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check root items (parent_id IS NULL or root category items)
    echo "<h3>Root Items (parent_id IS NULL):</h3>";
    $roots = \App\Core\Database::query("SELECT id, name, category_id, parent_id, level FROM budget_category_items WHERE parent_id IS NULL ORDER BY sort_order, id");
    echo "<pre>" . json_encode($roots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // Check items by category_id
    echo "<h3>Distinct category_id values:</h3>";
    $catIds = \App\Core\Database::query("SELECT DISTINCT category_id FROM budget_category_items ORDER BY category_id");
    echo "<pre>" . json_encode($catIds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

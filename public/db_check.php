<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Tables - hr_budget</title>
    <style>
        body { font-family: 'Courier New', monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        h1 { color: #4ade80; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #2a2a2a; }
        .info { background: #2a2a2a; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>📊 hr_budget Database Tables</h1>
    
    <?php
    try {
        $db = Database::getPdo();
        
        // Get all tables
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>Found <strong>" . count($tables) . "</strong> tables</div>";
        
        echo "<table>";
        echo "<tr><th>#</th><th>Table Name</th><th>Rows</th><th>Columns</th></tr>";
        
        foreach ($tables as $i => $table) {
            // Count rows
            $countStmt = $db->query("SELECT COUNT(*) as cnt FROM `$table`");
            $rowCount = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            
            // Count columns
            $colStmt = $db->query("SHOW COLUMNS FROM `$table`");
            $colCount = $colStmt->rowCount();
            
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$rowCount</td>";
            echo "<td>$colCount</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check specific table
        echo "<h2>🔍 budget_category_items Details</h2>";
        
        $check = $db->query("SHOW TABLES LIKE 'budget_category_items'");
        if ($check->rowCount() > 0) {
            echo "<div class='info' style='background: #1e3a1e;'>✅ Table EXISTS</div>";
            
            $stmt = $db->query("SELECT * FROM budget_category_items ORDER BY category_id, sort_order");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>Total items: <strong>" . count($items) . "</strong></p>";
            
            if (count($items) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Category ID</th><th>Item Name</th><th>Unit</th><th>Requires Qty</th></tr>";
                foreach ($items as $item) {
                    echo "<tr>";
                    echo "<td>{$item['id']}</td>";
                    echo "<td>{$item['category_id']}</td>";
                    echo "<td>{$item['item_name']}</td>";
                    echo "<td>{$item['default_unit']}</td>";
                    echo "<td>" . ($item['requires_quantity'] ? 'Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<div class='info' style='background: #3a1e1e;'>❌ Table DOES NOT EXIST</div>";
        }
        
        // Check column
        echo "<h2>🔍 budget_request_items.category_item_id</h2>";
        $colCheck = $db->query("SHOW COLUMNS FROM budget_request_items LIKE 'category_item_id'");
        if ($colCheck->rowCount() > 0) {
            echo "<div class='info' style='background: #1e3a1e;'>✅ Column EXISTS</div>";
        } else {
            echo "<div class='info' style='background: #3a1e1e;'>❌ Column DOES NOT EXIST</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='info' style='background: #3a1e1e;'>❌ Error: " . $e->getMessage() . "</div>";
    }
    ?>
</body>
</html>

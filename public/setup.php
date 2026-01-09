<?php
/**
 * Web-accessible setup script for Phase 3 Inline Items
 * Visit: http://localhost/hr_budget/public/setup.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Phase 3 - Budget Items</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #0ff; }
    </style>
</head>
<body>
<h1>Phase 3 Setup - Hierarchical Budget Items</h1>
<pre>
<?php

try {
    $db = Database::getPdo();
    echo "<span class='info'>✓ Database connected</span>\n\n";
    
    // 1. Drop and recreate table
    echo "Dropping old budget_category_items table...\n";
    $db->exec("DROP TABLE IF EXISTS budget_category_items");
    echo "<span class='success'>✓ Dropped</span>\n\n";
    
    echo "Creating new budget_category_items table with hierarchy support...\n";
    $sql = "CREATE TABLE budget_category_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        item_code VARCHAR(50),
        item_name VARCHAR(255) NOT NULL,
        description TEXT,
        default_unit VARCHAR(50),
        requires_quantity BOOLEAN DEFAULT TRUE,
        is_header BOOLEAN DEFAULT FALSE,
        level INT DEFAULT 0,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (category_id),
        INDEX (item_code)
    )";
    $db->exec($sql);
    echo "<span class='success'>✓ Table created</span>\n\n";
    
    // 2. Add remark column to budget_request_items
    echo "Adding 'remark' column to budget_request_items...\n";
    $checkRemark = $db->query("SHOW COLUMNS FROM budget_request_items LIKE 'remark'");
    if ($checkRemark->rowCount() === 0) {
        $db->exec("ALTER TABLE budget_request_items ADD COLUMN remark TEXT NULL");
        echo "<span class='success'>✓ Column added</span>\n";
    } else {
        echo "<span class='info'>✓ Column already exists</span>\n";
    }
    
    // 3. Clear old request items for Request #5
    echo "\nClearing old items for Request #5...\n";
    $count = $db->exec("DELETE FROM budget_request_items WHERE budget_request_id = 5");
    echo "<span class='success'>✓ Deleted $count old items</span>\n\n";
    
    // 4. Find Personnel category
    $stmt = $db->query("SELECT id, name_th FROM budget_categories WHERE name_th LIKE '%บุคลากร%' LIMIT 1");
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cat) {
        echo "<span class='error'>✗ Personnel category not found!</span>\n";
        exit;
    }
    
    $catId = $cat['id'];
    echo "Found category: {$cat['name_th']} (ID: $catId)\n\n";
    
    // 5. Seed data
    echo "Seeding hierarchical budget items...\n";
    
    $items = [
        ['1', 'งบบุคลากร', true, 0, false],
        ['1.1', 'เงินเดือน รวม', true, 1, false],
        ['1.1.1', 'เงินเดือน', true, 2, false],
        ['1', 'อัตราเดิม', false, 3, true],
        ['2', 'อัตราใหม่', false, 3, true],
        ['1.1.2', 'เงินอื่นที่จ่ายควบกับเงินเดือน', true, 2, false],
        ['1', 'เงินประจำตำแหน่ง รวม', true, 3, false],
        ['1.1', 'เงินประจำตำแหน่ง (บริหารและอำนวยการ)', false, 4, true],
        ['1.2', 'เงินประจำตำแหน่ง (วิชาการ)', false, 4, true],
        ['1.3', 'เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ', false, 4, true],
        ['2', 'ค่าตอบแทนรายเดือน', true, 3, false],
        ['3', 'เงินช่วยค่าครองชีพ/เงินพิเศษอื่นๆ', true, 3, false],
        ['3.1', 'เงินช่วยเหลือการครองชีพ', false, 4, true],
        ['3.2', 'เงิน พ.ต.ก. (นิติกร)', false, 4, true],
        ['3.3', 'เงิน พ.ต.ส. (พัสดุ)', false, 4, true],
        ['1.2', 'ค่าจ้างประจำ รวม', true, 1, false],
        ['1.2.1', 'ค่าจ้างประจำ', true, 2, false],
        ['1', 'อัตราเดิม', false, 3, true],
        ['2', 'อัตราใหม่', false, 3, true],
        ['1.3', 'ค่าตอบแทนพนักงานราชการ รวม', true, 1, false],
        ['1.3.1', 'ค่าตอบแทนพนักงานราชการ', true, 2, false],
        ['1', 'อัตราเดิม', false, 3, true],
        ['2', 'อัตราใหม่', false, 3, true],
    ];
    
    $insertSql = "INSERT INTO budget_category_items (category_id, item_code, item_name, is_header, level, requires_quantity, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertSql);
    
    foreach ($items as $index => $item) {
        $stmt->execute([
            $catId,
            $item[0], // code
            $item[1], // name
            $item[2] ? 1 : 0, // is_header
            $item[3], // level
            $item[4] ? 1 : 0, // requires_quantity
            $index + 1 // sort_order
        ]);
    }
    
    echo "<span class='success'>✓ Seeded " . count($items) . " items</span>\n\n";
    
    // 6. Verify
    $count = $db->query("SELECT COUNT(*) FROM budget_category_items")->fetchColumn();
    echo "Total master items in database: <span class='success'>$count</span>\n\n";
    
    echo "<span class='success'>==========================================</span>\n";
    echo "<span class='success'>✓ SETUP COMPLETED SUCCESSFULLY!</span>\n";
    echo "<span class='success'>==========================================</span>\n\n";
    
    echo "<span class='info'>Next step: Refresh the page at:</span>\n";
    echo "<a href='/hr_budget/public/requests/5' style='color: #0ff;'>http://localhost/hr_budget/public/requests/5</a>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>✗ ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>" . $e->getTraceAsString() . "</span>\n";
}

?>
</pre>
</body>
</html>

<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "Setting up Budget Category Items...\n\n";

$db = Database::getPdo();

try {
    // 1. Create budget_category_items table
    echo "1. Creating budget_category_items table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS budget_category_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        item_code VARCHAR(50),
        item_name VARCHAR(255) NOT NULL,
        description TEXT,
        default_unit VARCHAR(50),
        requires_quantity BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (category_id)
    )";
    $db->exec($sql);
    echo "   ✓ Table created successfully!\n\n";

    // 2. Add category_item_id column
    echo "2. Adding category_item_id column to budget_request_items...\n";
    try {
        $db->exec("ALTER TABLE budget_request_items ADD COLUMN category_item_id INT NULL AFTER request_id");
        echo "   ✓ Column added successfully!\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ⚠ Column already exists, skipping.\n";
        } else {
            throw $e;
        }
    }

    try {
        $db->exec("CREATE INDEX idx_req_item_cat_item ON budget_request_items(category_item_id)");
        echo "   ✓ Index created successfully!\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false) {
            echo "   ⚠ Index already exists, skipping.\n\n";
        } else {
            throw $e;
        }
    }

    // 3. Seed Personnel Budget Items
    echo "3. Seeding personnel budget items...\n";
    
    // Find personnel category
    $stmt = $db->query("SELECT id, name_th FROM budget_categories WHERE name_th LIKE '%บุคลากร%' LIMIT 1");
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cat) {
        $catId = $cat['id'];
        echo "   Found category: {$cat['name_th']} (ID: {$catId})\n";

        // Clear existing
        $db->exec("DELETE FROM budget_category_items WHERE category_id = $catId");

        // Insert items
        $items = [
            ['เงินเดือน (ฝ่ายการพนักงาน)', 'คน', true, 1],
            ['ค่าจ้างประจำ', 'คน', true, 2],
            ['ค่าจ้างชั่วคราว', 'คน', true, 3],
            ['ค่าตอบแทนพนักงานราชการ', 'คน', true, 4],
            ['เงินประจำตำแหน่ง', 'คน', true, 5],
            ['ค่าตอบแทนพิเศษ', 'คน', true, 6],
            ['ค่าครองชีพชั่วคราว', 'คน', true, 7],
            ['ค่าล่วงเวลา', 'บาท', false, 8],
            ['ค่ารักษาพยาบาล', 'บาท', false, 9],
            ['ค่าเล่าเรียนบุตร', 'บาท', false, 10],
            ['เงินช่วยเหลือบุตร', 'บาท', false, 11],
            ['เงินสมทบกองทุนประกันสังคม', 'บาท', false, 12]
        ];

        $insertSql = "INSERT INTO budget_category_items (category_id, item_name, default_unit, requires_quantity, sort_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertSql);

        foreach ($items as $item) {
            $stmt->execute([
                $catId,
                $item[0], // name
                $item[1], // unit
                $item[2] ? 1 : 0, // requires_quantity
                $item[3] // sort_order
            ]);
        }

        echo "   ✓ Inserted " . count($items) . " personnel items!\n\n";
    } else {
        echo "   ⚠ Personnel category not found!\n\n";
    }

    echo "=================================\n";
    echo "✅ Setup completed successfully!\n";
    echo "=================================\n\n";
    echo "Next: Visit http://localhost/hr_budget/public/db_check.php to verify\n";

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
/**
 * Exact Budget Items Seeder - Matches form_req.jsx EXACTLY
 * 
 * Structure from React:
 * - Personnel (งบบุคลากร): 6 sections, 24 fields
 * - Operations (งบดำเนินงาน): 2 sections, 6 fields
 * Total: 8 sections, 30 editable fields
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getPdo();

echo "=== Budget Items Seeder (Exact Match to form_req.jsx) ===\n\n";

try {
    // 1. Drop and recreate the items table
    echo "[1] Resetting budget_category_items table...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("DROP TABLE IF EXISTS budget_category_items");
    $db->exec("CREATE TABLE budget_category_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        item_code VARCHAR(50),
        item_name VARCHAR(255) NOT NULL,
        is_header BOOLEAN DEFAULT FALSE,
        level INT DEFAULT 0,
        requires_quantity BOOLEAN DEFAULT TRUE,
        default_unit VARCHAR(50) DEFAULT 'คน',
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Also reset request items
    $db->exec("DROP TABLE IF EXISTS budget_request_items");
    $db->exec("CREATE TABLE budget_request_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        budget_request_id INT NOT NULL,
        category_item_id INT,
        item_name VARCHAR(255),
        quantity DECIMAL(15,2) DEFAULT 0,
        unit_price DECIMAL(15,2) DEFAULT 0,
        total_amount DECIMAL(15,2) DEFAULT 0,
        remark TEXT,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (budget_request_id),
        INDEX (category_item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "    Tables reset successfully.\n\n";

    // 2. Ensure categories exist
    echo "[2] Setting up categories...\n";
    $categoryDefs = [
        'personnel' => ['name_th' => 'งบบุคลากร', 'desc' => 'ค่าใช้จ่ายเกี่ยวกับบุคลากร เงินเดือน และค่าจ้าง'],
        'operations' => ['name_th' => 'งบดำเนินงาน', 'desc' => 'ค่าตอบแทน ค่าใช้สอย และวัสดุอุปกรณ์']
    ];
    
    $catIds = [];
    foreach ($categoryDefs as $key => $def) {
        $stmt = $db->prepare("SELECT id FROM budget_categories WHERE name_th = ?");
        $stmt->execute([$def['name_th']]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cat) {
            $ins = $db->prepare("INSERT INTO budget_categories (name_th, code, description, level) VALUES (?, ?, ?, 0)");
            $ins->execute([$def['name_th'], strtoupper($key), $def['desc']]);
            $catIds[$key] = $db->lastInsertId();
            echo "    Created: {$def['name_th']} (ID: {$catIds[$key]})\n";
        } else {
            $catIds[$key] = $cat['id'];
            // Update description to match
            $upd = $db->prepare("UPDATE budget_categories SET description = ? WHERE id = ?");
            $upd->execute([$def['desc'], $cat['id']]);
            echo "    Found: {$def['name_th']} (ID: {$cat['id']})\n";
        }
    }
    echo "\n";

    // 3. Define items EXACTLY as in form_req.jsx
    // Format: [item_code, item_name, is_header, level]
    // Headers (sections) are level 1, fields are level 2
    
    echo "[3] Inserting items...\n";
    
    // PERSONNEL CATEGORY
    $personnelItems = [
        // Root header (level 0)
        ['ROOT', 'งบบุคลากร', true, 0],
        
        // Section 1: Salary (เงินเดือน) - 2 fields
        ['salary', 'เงินเดือน', true, 1],
        ['salary_old_rate', 'อัตราเดิม', false, 2],
        ['salary_new_rate', 'อัตราใหม่', false, 2],
        
        // Section 2: Position (เงินประจำตำแหน่ง) - 4 fields
        ['position', 'เงินประจำตำแหน่ง', true, 1],
        ['pos_admin', 'บริหารและอำนวยการ', false, 2],
        ['pos_academic', 'วิชาการ', false, 2],
        ['pos_computer', 'วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์', false, 2],
        ['pos_engineer', 'วิชาชีพเฉพาะ - วิศวกร/สถาปนิก', false, 2],
        
        // Section 3: Compensation (ค่าตอบแทนรายเดือน) - 5 fields
        ['compensation', 'ค่าตอบแทนรายเดือน', true, 1],
        ['comp_admin', 'บริหารและอำนวยการ', false, 2],
        ['comp_academic', 'วิชาการ', false, 2],
        ['comp_computer', 'วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์', false, 2],
        ['comp_engineer', 'วิชาชีพเฉพาะ - วิศวกร/สถาปนิก', false, 2],
        ['comp_level8', 'ข้าราชการระดับ 8 และ 8ว', false, 2],
        
        // Section 4: Special (เงินเพิ่มอื่นๆ) - 5 fields
        ['special', 'เงินเพิ่มอื่นๆ', true, 1],
        ['living_assist', 'เงินช่วยเหลือการครองชีพข้าราชการระดับต้น', false, 2],
        ['ptk', 'พ.ต.ก. (ผู้ปฏิบัติงานด้านนิติกร)', false, 2],
        ['ppd', 'พ.พ.ด. (ผู้ปฏิบัติงานด้านพัสดุ)', false, 2],
        ['psr', 'พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)', false, 2],
        ['spp', 'สปพ. (สวัสดิการพื้นที่พิเศษ)', false, 2],
        
        // Section 5: Permanent (ค่าจ้างประจำ) - 5 fields
        ['permanent', 'ค่าจ้างประจำ', true, 1],
        ['perm_old', 'อัตราเดิม', false, 2],
        ['perm_new', 'อัตราใหม่', false, 2],
        ['perm_monthly', 'ค่าตอบแทนรายเดือนลูกจ้างประจำ', false, 2],
        ['perm_living', 'เงินช่วยเหลือค่าครองชีพ', false, 2],
        ['perm_psr', 'พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)', false, 2],
        
        // Section 6: Gov Employee (ค่าตอบแทนพนักงานราชการ) - 3 fields
        ['govEmployee', 'ค่าตอบแทนพนักงานราชการ', true, 1],
        ['gov_old', 'อัตราเดิม', false, 2],
        ['gov_new', 'อัตราใหม่', false, 2],
        ['gov_temp', 'เงินช่วยเหลือการครองชีพชั่วคราว', false, 2],
    ];
    
    // OPERATIONS CATEGORY
    $operationsItems = [
        // Root header (level 0)
        ['ROOT', 'งบดำเนินงาน', true, 0],
        
        // Section 1: Operations/Compensation (ค่าตอบแทน) - 4 fields
        ['operations', 'ค่าตอบแทน', true, 1],
        ['rent', 'ค่าเช่าบ้าน', false, 2],
        ['full_salary', 'ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น', false, 2],
        ['full_wage', 'ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น', false, 2],
        ['south_special', 'ค่าตอบแทนพิเศษรายเดือน (จังหวัดชายแดนภาคใต้)', false, 2],
        
        // Section 2: Expenses (ค่าใช้สอย) - 2 fields
        ['expenses', 'ค่าใช้สอย', true, 1],
        ['social_security', 'เงินสมทบกองทุนประกันสังคม', false, 2],
        ['compensation_fund', 'เงินสมทบกองทุนเงินทดแทน', false, 2],
    ];

    $insertStmt = $db->prepare("INSERT INTO budget_category_items 
        (category_id, item_code, item_name, is_header, level, requires_quantity, sort_order) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Insert Personnel items
    $personnelCount = 0;
    $personnelFields = 0;
    foreach ($personnelItems as $idx => $item) {
        $requiresQty = !$item[2]; // Non-headers require quantity
        $insertStmt->execute([
            $catIds['personnel'],
            $item[0],
            $item[1],
            $item[2] ? 1 : 0,
            $item[3],
            $requiresQty ? 1 : 0,
            $idx + 1
        ]);
        $personnelCount++;
        if (!$item[2]) $personnelFields++;
    }
    echo "    Personnel: $personnelCount items ($personnelFields editable fields)\n";
    
    // Insert Operations items
    $operationsCount = 0;
    $operationsFields = 0;
    foreach ($operationsItems as $idx => $item) {
        $requiresQty = !$item[2];
        $insertStmt->execute([
            $catIds['operations'],
            $item[0],
            $item[1],
            $item[2] ? 1 : 0,
            $item[3],
            $requiresQty ? 1 : 0,
            $idx + 1
        ]);
        $operationsCount++;
        if (!$item[2]) $operationsFields++;
    }
    echo "    Operations: $operationsCount items ($operationsFields editable fields)\n";
    
    $totalItems = $personnelCount + $operationsCount;
    $totalFields = $personnelFields + $operationsFields;
    echo "\n";

    // 4. Verify
    echo "[4] Verification...\n";
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM budget_category_items");
    $dbCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM budget_category_items WHERE is_header = 0");
    $dbFields = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    echo "    Database has: $dbCount items ($dbFields editable fields)\n";
    
    if ($dbCount == $totalItems && $dbFields == $totalFields) {
        echo "\n✅ SUCCESS! All items inserted correctly.\n";
        echo "   Expected: $totalItems items, $totalFields fields\n";
        echo "   Got:      $dbCount items, $dbFields fields\n";
    } else {
        echo "\n❌ MISMATCH!\n";
        echo "   Expected: $totalItems items, $totalFields fields\n";
        echo "   Got:      $dbCount items, $dbFields fields\n";
    }
    
    // Show breakdown by category
    echo "\n[5] Category Breakdown:\n";
    $stmt = $db->query("
        SELECT bc.name_th, 
               COUNT(bci.id) as total_items,
               SUM(CASE WHEN bci.is_header = 0 THEN 1 ELSE 0 END) as fields,
               SUM(CASE WHEN bci.level = 1 THEN 1 ELSE 0 END) as sections
        FROM budget_categories bc
        LEFT JOIN budget_category_items bci ON bc.id = bci.category_id
        GROUP BY bc.id, bc.name_th
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "    {$row['name_th']}: {$row['total_items']} items, {$row['sections']} sections, {$row['fields']} fields\n";
    }

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Seeding Complete ===\n";
?>

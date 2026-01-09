<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getPdo();

echo "Setting up Budget Category Items (Exact Match to React)...\n";

try {
    // 1. Reset Tables
    $db->exec("DROP TABLE IF EXISTS budget_category_items");
    
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
    
    // 2. Ensure Categories Exist
    $categories = [
        'personnel' => 'งบบุคลากร',
        'operations' => 'งบดำเนินงาน'
    ];
    
    $catIds = [];
    
    foreach ($categories as $key => $name) {
        $stmt = $db->prepare("SELECT id FROM budget_categories WHERE name LIKE ?");
        $stmt->execute(["%$name%"]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cat) {
            $db->prepare("INSERT INTO budget_categories (name, code, description) VALUES (?, ?, ?)")
               ->execute([$name, strtoupper($key), $name]);
            $catIds[$key] = $db->lastInsertId();
            echo "Created Category: $name\n";
        } else {
            $catIds[$key] = $cat['id'];
            echo "Found Category: $name (ID: {$cat['id']})\n";
        }
    }

    // 3. Define Items (Structure: [Name, IsHeader, Level, RequiresQty, Unit])
    
    // PERSONNEL items
    $personnelItems = [
        ['งบบุคลากร', true, 0, false, null], // Root
        
        // Section: Salary
        ['เงินเดือน', true, 1, false, null],
        ['อัตราเดิม', false, 2, true, 'คน'],
        ['อัตราใหม่', false, 2, true, 'คน'],
        
        // Section: Position
        ['เงินประจำตำแหน่ง', true, 1, false, null],
        ['บริหารและอำนวยการ', false, 2, true, 'คน'],
        ['วิชาการ', false, 2, true, 'คน'],
        ['วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์', false, 2, true, 'คน'],
        ['วิชาชีพเฉพาะ - วิศวกร/สถาปนิก', false, 2, true, 'คน'],
        
        // Section: Compensation
        ['ค่าตอบแทนรายเดือน', true, 1, false, null],
        ['บริหารและอำนวยการ', false, 2, true, 'คน'],
        ['วิชาการ', false, 2, true, 'คน'],
        ['วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์', false, 2, true, 'คน'],
        ['วิชาชีพเฉพาะ - วิศวกร/สถาปนิก', false, 2, true, 'คน'],
        ['ข้าราชการระดับ 8 และ 8ว', false, 2, true, 'คน'],
        
        // Section: Special
        ['เงินเพิ่มพิเศษ', true, 1, false, null],
        ['เงินช่วยเหลือการครองชีพข้าราชการระดับต้น', false, 2, true, 'คน'],
        ['พ.ต.ก. (ผู้ปฏิบัติงานด้านนิติกร)', false, 2, true, 'คน'],
        ['พ.พ.ด. (ผู้ปฏิบัติงานด้านพัสดุ)', false, 2, true, 'คน'],
        ['พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)', false, 2, true, 'คน'],
        ['สปพ. (สวัสดิการพื้นที่พิเศษ)', false, 2, true, 'คน'],
        
        // Section: Permanent Wages
        ['ค่าจ้างประจำ', true, 1, false, null],
        ['อัตราเดิม', false, 2, true, 'คน'],
        ['อัตราใหม่', false, 2, true, 'คน'],
        ['ค่าตอบแทนรายเดือนลูกจ้างประจำ', false, 2, true, 'คน'],
        ['เงินช่วยเหลือค่าครองชีพ', false, 2, true, 'คน'],
        ['พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)', false, 2, true, 'คน'],
        
        // Section: Gov Employees
        ['ค่าตอบแทนพนักงานราชการ', true, 1, false, null],
        ['อัตราเดิม', false, 2, true, 'คน'],
        ['อัตราใหม่', false, 2, true, 'คน'],
        ['เงินช่วยเหลือการครองชีพชั่วคราว', false, 2, true, 'คน'],
    ];

    // OPERATIONS items
    $operationsItems = [
        ['งบดำเนินงาน', true, 0, false, null], // Root
        
        // Section: Operations (Compensation) - Note: React calls it 'operations' key but title 'ค่าตอบแทน'
        ['ค่าตอบแทน', true, 1, false, null],
        ['ค่าเช่าบ้าน', false, 2, true, 'คน'],
        ['ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น', false, 2, true, 'คน'],
        ['ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น', false, 2, true, 'คน'],
        ['ค่าตอบแทนพิเศษรายเดือน (จังหวัดชายแดนภาคใต้)', false, 2, true, 'คน'],
        
        // Section: Expenses
        ['ค่าใช้สอย', true, 1, false, null],
        ['เงินสมทบกองทุนประกันสังคม', false, 2, true, 'คน'],
        ['เงินสมทบกองทุนเงินทดแทน', false, 2, true, 'คน'],
    ];

    $insertStmt = $db->prepare("INSERT INTO budget_category_items (category_id, item_name, is_header, level, requires_quantity, default_unit, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Insert Personnel
    foreach ($personnelItems as $idx => $item) {
        $insertStmt->execute([
            $catIds['personnel'],
            $item[0],
            $item[1],
            $item[2],
            $item[3],
            $item[4],
            $idx + 1
        ]);
    }
    echo "Inserted Personnel Items.\n";

    // Insert Operations
    foreach ($operationsItems as $idx => $item) {
        $insertStmt->execute([
            $catIds['operations'],
            $item[0],
            $item[1],
            $item[2],
            $item[3],
            $item[4],
            $idx + 1
        ]);
    }
    echo "Inserted Operations Items.\n";
    
    // Clear old request items to prevent orphans (Optional, but good for cleanliness)
    $db->exec("TRUNCATE TABLE budget_request_items");
    echo "Cleared old request items to force reload.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
?>

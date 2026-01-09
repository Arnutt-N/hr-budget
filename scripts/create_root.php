<?php
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/config/database.php';

use App\Core\Database;

echo "=== Creating Root Category ===\n\n";

try {
    $db = Database::getInstance();
    
    // 1. Check if already exists
    $stmt = $db->prepare("SELECT id FROM budget_categories WHERE code = ?");
    $stmt->execute(['GOVT_PERSONNEL_EXP']);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        echo "[OK] Root category already exists (ID: {$exists['id']})\n";
        $rootId = $exists['id'];
    } else {
        // 2. Create root category
        echo "Creating root category...\n";
        $stmt = $db->prepare("
            INSERT INTO budget_categories 
            (code, name_th, name_en, description, parent_id, level, sort_order, is_active) 
            VALUES (?, ?, ?, ?, NULL, 0, 0, 1)
        ");
        
        $stmt->execute([
            'GOVT_PERSONNEL_EXP',
            'รายการค่าใช้จ่ายบุคลากรภาครัฐ',
            'Government Personnel Expenditure',
            'หมวดหมู่หลักสำหรับค่าใช้จ่ายบุคลากรภาครัฐทั้งหมด'
        ]);
        
        $rootId = $db->lastInsertId();
        echo "[SUCCESS] Created root category (ID: $rootId)\n";
    }
    
    echo "\n=== Updating Parent Links ===\n";
    
    // 3. Update งบบุคลากร
    $stmt = $db->prepare("UPDATE budget_categories SET parent_id = ? WHERE code = '1'");
    $stmt->execute([$rootId]);
    echo "Updated งบบุคลากร (Code: 1) -> Parent: $rootId\n";
    
    // 4. Update งบดำเนินงาน
    $stmt = $db->prepare("UPDATE budget_categories SET parent_id = ? WHERE code = 'OPERATIONS'");
    $stmt->execute([$rootId]);
    echo "Updated งบดำเนินงาน (Code: OPERATIONS) -> Parent: $rootId\n";
    
    // 5. Update งบดำเนินงาน level if needed
    $stmt = $db->prepare("SELECT level FROM budget_categories WHERE code = 'OPERATIONS'");
    $stmt->execute();
    $op = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($op && $op['level'] == 0) {
        echo "\nAdjusting งบดำเนินงาน level from 0 to 1...\n";
        $stmt = $db->prepare("UPDATE budget_categories SET level = 1 WHERE code = 'OPERATIONS'");
        $stmt->execute();
        echo "[SUCCESS] Level updated\n";
    }
    
    echo "\n=== COMPLETE ===\n";
    echo "Run: php final_verify.php to confirm\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}

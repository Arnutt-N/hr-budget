<?php
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/config/database.php';

use App\Core\Database;

echo "Starting Recovery Migration...\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 1. Check if Root Category exists
    $stmt = $pdo->prepare("SELECT id FROM budget_categories WHERE code = ?");
    $stmt->execute(['GOVT_PERSONNEL_EXP']);
    $root = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $rootId = null;
    
    if (!$root) {
        echo "- Root category not found. Creating...\n";
        $stmt = $pdo->prepare("INSERT INTO budget_categories (code, name_th, name_en, description, parent_id, level, sort_order, is_active) VALUES (?, ?, ?, ?, NULL, 0, 0, 1)");
        $stmt->execute([
            'GOVT_PERSONNEL_EXP', 
            'รายการค่าใช้จ่ายบุคลากรภาครัฐ', 
            'Government Personnel Expenditure', 
            'หมวดหมู่หลักสำหรับค่าใช้จ่ายบุคลากรภาครัฐทั้งหมด'
        ]);
        $rootId = $pdo->lastInsertId();
        echo "  > Created Root ID: $rootId\n";
    } else {
        $rootId = $root['id'];
        echo "- Root category found ID: $rootId\n";
    }
    
    // 2. Update levels (Safety check: only if OPERATIONS is still level 0)
    $stmt = $pdo->prepare("SELECT level FROM budget_categories WHERE code = 'OPERATIONS'");
    $stmt->execute();
    $op = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($op && $op['level'] == 0) {
        echo "- Deteced old structure. Updating levels...\n";
        
        // Update from bottom to top to avoid unique constraints or logic issues
        // Max level assumption: 5
        for ($i = 5; $i >= 0; $i--) {
            $currentLevel = $i;
            $newLevel = $i + 1;
            $sql = "UPDATE budget_categories SET level = $newLevel WHERE level = $currentLevel AND code != 'GOVT_PERSONNEL_EXP'";
            $affected = $pdo->exec($sql);
            echo "  > Level $currentLevel -> $newLevel: $affected rows updated.\n";
        }
    } else {
        echo "- Levels seem already updated or OPERATIONS not at level 0.\n";
    }

    // 3. Link Top-Level Categories to Root
    echo "- Linking categories to Root ID: $rootId\n";
    $stmt = $pdo->prepare("UPDATE budget_categories SET parent_id = ? WHERE code IN ('1', 'OPERATIONS') AND (parent_id IS NULL OR parent_id != ?)");
    $stmt->execute([$rootId, $rootId]);
    echo "  > Updated " . $stmt->rowCount() . " categories.\n";

    echo "\nRecovery Completed Successfully.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

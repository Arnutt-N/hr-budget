<?php
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=hr_budget;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Running Migration: manual_add_special_professions.sql ===\n";
    
    $sqlFile = 'database/migrations/manual_add_special_professions.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    echo "✅ Migration executed successfully.\n";

    // Verify
    echo "\n=== Verification ===\n";
    $searchParent = '%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)%';
    $stmt = $pdo->prepare("SELECT id, name FROM budget_category_items WHERE name LIKE ?");
    $stmt->execute([$searchParent]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($parent) {
        echo "Parent: " . $parent['name'] . " (ID: " . $parent['id'] . ")\n";
        
        $stmtChildren = $pdo->prepare("SELECT id, name FROM budget_category_items WHERE parent_id = ? ORDER BY id");
        $stmtChildren->execute([$parent['id']]);
        $children = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($children) > 0) {
            echo "Children:\n";
            foreach ($children as $child) {
                echo " - [OK] " . $child['name'] . " (ID: " . $child['id'] . ")\n";
            }
        } else {
            echo "❌ Verify Failed: No children found under this parent.\n";
        }
    } else {
        echo "❌ Verify Failed: Parent category not found.\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

<?php
// C:\laragon\www\hr_budget\public\run_add_item_v2.php
header("Cache-Control: no-cache, must-revalidate");
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

try {
    $pdo = Database::getInstance();
    $pdo->beginTransaction();

    // 1. Insert Expense Item
    $parentId = 39; // ค่าใช้สอย
    $groupId = 3;   // ค่าตอบแทนใช้สอยและวัสดุ
    $name = 'เงินสมทบกองทุนเงินทดแทน';
    
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM expense_items WHERE name_th = ? AND parent_id = ?");
    $stmt->execute([$name, $parentId]);
    if ($stmt->fetch()) {
        echo "Item already exists.<br>";
        $pdo->rollBack();
        exit;
    }

    // Get parent level
    $stmt = $pdo->prepare("SELECT level FROM expense_items WHERE id = ?");
    $stmt->execute([$parentId]);
    $parentLevel = $stmt->fetchColumn();
    $level = $parentLevel + 1;
    
    // Get next sort order
    $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM expense_items WHERE parent_id = ?");
    $stmt->execute([$parentId]);
    $maxSort = $stmt->fetchColumn() ?: 0;
    $sortOrder = $maxSort + 1;

    // Insert
    $stmt = $pdo->prepare("INSERT INTO expense_items (expense_group_id, parent_id, name_th, level, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$groupId, $parentId, $name, $level, $sortOrder]);
    $newItemId = $pdo->lastInsertId();
    echo "Inserted Item ID: $newItemId<br>";

    // 2. Insert Budget Line Item for Org 3
    // Find sample from Item 40 (ประกันสังคม)
    $siblingId = 40;
    $orgId = 3;
    
    $stmt = $pdo->prepare("SELECT plan_id, project_id, activity_id FROM budget_line_items WHERE expense_item_id = ? AND division_id = ? LIMIT 1");
    $stmt->execute([$siblingId, $orgId]);
    $ref = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ref) {
        $stmt = $pdo->prepare("INSERT INTO budget_line_items (
            plan_id, project_id, activity_id, division_id, expense_type_id, expense_group_id, expense_item_id, 
            amount, allocated_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0)");
        
        $stmt->execute([
            $ref['plan_id'], $ref['project_id'], $ref['activity_id'], $orgId, 
            2, $groupId, $newItemId
        ]);
        echo "Inserted Budget Line Item for Org 3<br>";
    } else {
        echo "Warning: Could not find reference for Item 40/Org 3.<br>";
    }

    $pdo->commit();
    echo "Success.";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}

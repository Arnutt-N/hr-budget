<?php
/**
 * Script: fix_budget_duplicates.php
 * Purpose: Deletes duplicate budget_trackings and adds a UNIQUE constraint.
 */

// 1. Database Connection
$config = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

try {
    $pdo->beginTransaction();

    echo "--- 1. Identifying duplicates to keep (latest ID per item/record) ---\n";
    // Get the IDs we want to KEEP (max ID for each unique pair)
    $stmt = $pdo->query("
        SELECT MAX(id) as keep_id 
        FROM budget_trackings 
        GROUP BY disbursement_record_id, expense_item_id
    ");
    $keepIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($keepIds)) {
        throw new Exception("No records found in budget_trackings.");
    }

    $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
    
    echo "--- 2. Deleting duplicates ---\n";
    // Delete anything that is NOT in our keep list
    $deleteStmt = $pdo->prepare("DELETE FROM budget_trackings WHERE id NOT IN ($placeholders)");
    $deleteStmt->execute($keepIds);
    echo "Rows deleted: " . $deleteStmt->rowCount() . "\n";

    echo "--- 3. Adding UNIQUE constraint to prevent future duplicates ---\n";
    // Check if index already exists to avoid error
    $checkIndex = $pdo->query("SHOW INDEX FROM budget_trackings WHERE Key_name = 'uidx_record_item'");
    if (!$checkIndex->fetch()) {
        $pdo->exec("ALTER TABLE budget_trackings ADD UNIQUE INDEX uidx_record_item (disbursement_record_id, expense_item_id)");
        echo "UNIQUE index 'uidx_record_item' added successfully.\n";
    } else {
        echo "UNIQUE index already exists.\n";
    }

    $pdo->commit();
    echo "\nSUCCESS: Cleanup and schema update completed.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
}

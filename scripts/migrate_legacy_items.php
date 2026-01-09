<?php
// scripts/migrate_legacy_items.php
// Script to migrate legacy budget_category_items to expense_items
// and update budget_trackings references.

$config = require __DIR__ . '/../config/database.php';
$host = $config['host'];
$dbname = $config['database'];
$username = $config['username'];
$password = $config['password'];
$charset = $config['charset'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";

    $pdo->beginTransaction();

    // Fetch all old items (budget_category_items has no FK to categories)
    $stmt = $pdo->query("SELECT id, name FROM budget_category_items WHERE deleted_at IS NULL");
    $oldItems = $stmt->fetchAll();

    echo "Found " . count($oldItems) . " legacy items.\n\n";

    $migratedCount = 0;
    $updatedTrackingCount = 0;

    foreach ($oldItems as $item) {
        $oldId = $item['id'];
        $name = trim($item['name']);
        
        // Skip if name is empty
        if (empty($name)) continue;

        // Try to find matching New Item by exact name
        $stmt = $pdo->prepare("SELECT id, expense_group_id FROM expense_items WHERE name_th = ? LIMIT 1");
        $stmt->execute([$name]);
        $newItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $newItemId = null;
        $groupId = null;

        if ($newItem) {
            $newItemId = $newItem['id'];
            $groupId = $newItem['expense_group_id'];
            echo "Matched: '$name' -> New ID: $newItemId\n";
        } else {
            echo "Skipping: '$name' (No match found)\n";
            continue; 
        }

        if ($newItemId && $groupId) {
            // Get Group and Type info
            $stmt = $pdo->prepare("SELECT g.id as group_id, t.id as type_id 
                                   FROM expense_groups g 
                                   JOIN expense_types t ON g.expense_type_id = t.id 
                                   WHERE g.id = ?");
            $stmt->execute([$groupId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($info) {
                // Check if budget_trackings has any record for this old item
                $chk = $pdo->prepare("SELECT COUNT(*) FROM budget_trackings WHERE budget_category_item_id = ?");
                $chk->execute([$oldId]);
                $trackCount = $chk->fetchColumn();
                
                if ($trackCount > 0) {
                    $upd = $pdo->prepare("UPDATE budget_trackings 
                                          SET expense_item_id = ?, 
                                              expense_group_id = ?, 
                                              expense_type_id = ? 
                                          WHERE budget_category_item_id = ?");
                    $upd->execute([$newItemId, $info['group_id'], $info['type_id'], $oldId]);
                    $count = $upd->rowCount();
                    if ($count > 0) {
                        $updatedTrackingCount += $count;
                        echo "  → Updated $count tracking record(s)\n";
                    }
                }
            }
            $migratedCount++;
        }
    }

    $pdo->commit();
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Migration Summary:\n";
    echo str_repeat("=", 50) . "\n";
    echo "- Total Legacy Items: " . count($oldItems) . "\n";
    echo "- Successfully Matched: $migratedCount\n";
    echo "- Tracking Records Updated: $updatedTrackingCount\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

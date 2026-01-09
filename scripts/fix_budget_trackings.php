<?php
/**
 * Migration: Fix budget_trackings table
 * 1. Remove duplicate rows (keep latest)
 * 2. Add Unique Index on (fiscal_year, budget_category_item_id)
 */
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Budget Trackings Fix Migration ===\n\n";

try {
    $db = Database::getInstance();
    
    // Step 1: Check for duplicates
    echo "Step 1: Checking for duplicates...\n";
    $sql = "SELECT fiscal_year, budget_category_item_id, COUNT(*) as cnt 
            FROM budget_trackings 
            GROUP BY fiscal_year, budget_category_item_id 
            HAVING cnt > 1";
    $duplicates = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "  ✓ No duplicates found.\n\n";
    } else {
        echo "  ⚠ Found " . count($duplicates) . " duplicate groups.\n";
        
        // Step 2: Delete duplicates (keep latest ID)
        echo "Step 2: Removing duplicates (keeping latest)...\n";
        $deleteSql = "DELETE t1 FROM budget_trackings t1
                      INNER JOIN budget_trackings t2 
                      WHERE t1.id < t2.id 
                        AND t1.fiscal_year = t2.fiscal_year 
                        AND t1.budget_category_item_id = t2.budget_category_item_id";
        $deleted = $db->exec($deleteSql);
        echo "  ✓ Deleted {$deleted} duplicate rows.\n\n";
    }
    
    // Step 3: Check if unique index already exists
    echo "Step 3: Checking for existing unique index...\n";
    $indexes = $db->query("SHOW INDEX FROM budget_trackings WHERE Key_name = 'unique_tracking'")->fetchAll();
    
    if (!empty($indexes)) {
        echo "  ✓ Unique index already exists.\n\n";
    } else {
        // Step 4: Add Unique Index
        echo "Step 4: Adding Unique Index...\n";
        $db->exec("ALTER TABLE budget_trackings ADD UNIQUE KEY unique_tracking (fiscal_year, budget_category_item_id)");
        echo "  ✓ Unique index 'unique_tracking' created.\n\n";
    }
    
    echo "=== Migration Complete ===\n";
    echo "The 'ON DUPLICATE KEY UPDATE' in BudgetController::saveTracking() will now work correctly.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

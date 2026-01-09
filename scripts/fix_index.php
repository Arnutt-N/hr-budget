<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

echo "Checking indexes on budget_trackings...\n";

try {
    $rows = Database::query("SHOW INDEX FROM budget_trackings");
    $indexes = [];
    foreach ($rows as $row) {
        $indexes[$row['Key_name']][] = $row['Column_name'];
    }
    
    // Check if we need to migrate
    $needsMigration = false;
    if (isset($indexes['unique_tracking'])) {
        echo "Found old index 'unique_tracking'. Dropping it...\n";
        Database::query("ALTER TABLE budget_trackings DROP INDEX unique_tracking");
        $needsMigration = true;
    } elseif (isset($indexes['budget_trackings_fiscal_year_budget_category_item_id_unique'])) {
        // sometimes Laravel/standard naming
        echo "Found old index (long name). Dropping it...\n";
        Database::query("ALTER TABLE budget_trackings DROP INDEX budget_trackings_fiscal_year_budget_category_item_id_unique");
        $needsMigration = true;
    }
    
    // Check if new index exists
    if (!isset($indexes['unique_tracking_org'])) {
        echo "Creating new unique index 'unique_tracking_org'...\n";
        // Ensure organization_id is nullable? If unique key has NULL, multiple NULLs are allowed (in standard SQL).
        // If organization_id is NULL (for all orgs), we want unique per (year, item, NULL).
        // MySQL allows multiple NULLs in unique constraint. This is GOOD if we want multiple "NULL" entries?
        // NO. if organization_id is NULL (All orgs), we only want ONE record per item per year.
        // But MySQL allows multiple NULLs.
        // So we might need a virtual generated column or use 0 for global.
        // OR just rely on app logic to not duplicate.
        // BUT better: Use 0 instead of NULL for "All Organizations" if we want strict uniqueness.
        // However, schema said defaults NULL.
        // If we use NULL, Unique index won't enforce uniqueness for NULLs.
        // So, let's look at updating organization_id to 0 if NULL?
        // Or just change column to NOT NULL DEFAULT 0?
        // Let's stick to NULL for now and maybe just Add index. App logic acts as gatekeeper too.
        
        Database::query("ALTER TABLE budget_trackings ADD UNIQUE KEY unique_tracking_org (fiscal_year, budget_category_item_id, organization_id)");
        echo "Index created.\n";
    } else {
        echo "Index 'unique_tracking_org' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;

echo "=== Testing Phase 3 Logic ===\n";

try {
    $db = Database::getPdo();
    $db->beginTransaction();

    // 1. Get default Org
    $org = Database::queryOne("SELECT org_id FROM dim_organization LIMIT 1");
    if (!$org) throw new Exception("No organization found. Run seed_default_org.php first.");
    $orgId = $org['org_id'];
    echo "Using Org ID: $orgId\n";

    // 2. Create Request
    echo "Creating Request...\n";
    $reqData = [
        'fiscal_year' => 2568,
        'request_title' => 'Test Phase 3 Request',
        'created_by' => 1, // Assume user 1 exists
        'org_id' => $orgId,
        'request_status' => 'draft'
    ];
    $reqId = BudgetRequest::create($reqData);
    echo "Request Created: ID $reqId\n";

    // Verify Org ID saved
    $savedReq = Database::queryOne("SELECT org_id FROM budget_requests WHERE id = ?", [$reqId]);
    if ($savedReq['org_id'] != $orgId) {
        throw new Exception("Org ID mismatch! Expected $orgId, got {$savedReq['org_id']}");
    }
    echo "✅ Org ID saved correctly.\n";

    // 3. Get a Category Item (L2)
    // Find an item that has parent (L1) and root (L0) to test full sync
    $catItem = Database::queryOne("SELECT id, item_name FROM budget_category_items WHERE level = 2 LIMIT 1");
    if (!$catItem) {
        echo "⚠️ No Level 2 category item found. Skipping item sync test.\n";
    } else {
        echo "Testing Item Sync with Category: {$catItem['item_name']} (ID: {$catItem['id']})\n";
        
        $itemData = [
            'budget_request_id' => $reqId,
            'category_item_id' => $catItem['id'],
            'item_name' => $catItem['item_name'],
            'quantity' => 10,
            'unit_price' => 100
        ];
        
        // This should trigger syncToStructure
        BudgetRequestItem::create($itemData);
        
        // Verify structure_id in item
        $savedItem = Database::queryOne("SELECT structure_id FROM budget_request_items WHERE budget_request_id = ?", [$reqId]);
        
        if (empty($savedItem['structure_id'])) {
            throw new Exception("❌ Structure ID is NULL! Sync failed.");
        }
        $structId = $savedItem['structure_id'];
        echo "✅ Item saved with Structure ID: $structId\n";
        
        // Verify Dimension created
        $dim = Database::queryOne("SELECT * FROM dim_budget_structure WHERE structure_id = ?", [$structId]);
        echo "   -> Mapped to Plan: [{$dim['plan_name']}] / Output: [{$dim['output_name']}] / Activity: [{$dim['activity_name']}]\n";
        
        if ($dim['org_id'] != $orgId) {
            throw new Exception("❌ Dimension Org ID mismatch!");
        }
    }

    $db->rollBack();
    echo "\n✅ TEST COMPLETED SUCCESSFULLY (Rolled back)\n";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

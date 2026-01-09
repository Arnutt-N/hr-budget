<?php
/**
 * Sync Logic Pseudocode for BudgetRequestItem
 * 
 * This logic will be implemented in BudgetRequestItem::syncToStructure()
 * to automatically create/find dim_budget_structure entries
 */

namespace App\Models;

use App\Core\Database;

class BudgetRequestItem
{
    /**
     * Sync a category item to dimensional structure
     * 
     * @param int $categoryItemId The budget_category_items.id
     * @param int $orgId The organization ID from the request
     * @return int The structure_id to save in budget_request_items
     */
    public static function syncToStructure(int $categoryItemId, int $orgId): int
    {
        // STEP 1: Get category item details
        $item = Database::queryOne(
            "SELECT id, item_name, level, category_id FROM budget_category_items WHERE id = ?",
            [$categoryItemId]
        );
        
        if (!$item) {
            throw new \Exception("Category item not found: $categoryItemId");
        }
        
        // STEP 2: Build hierarchy path by walking up the tree
        $hierarchy = self::buildHierarchyPath($categoryItemId);
        // Result: ['งบบุคลากร' (L0), 'เงินเดือน' (L1), 'อัตราเดิม' (L2)]
        
        // STEP 3: Map to dimensional fields
        $planName = $hierarchy[0] ?? null;      // L0 -> plan_name
        $outputName = $hierarchy[1] ?? null;    // L1 -> output_name
        $activityName = $hierarchy[2] ?? null;  // L2 -> activity_name
        $itemName = $hierarchy[2] ?? null;      // L2 -> item_name (same as activity)
        
        // STEP 4: Check if this structure already exists
        $existing = Database::queryOne(
            "SELECT structure_id FROM dim_budget_structure 
             WHERE plan_name = ? AND output_name = ? AND activity_name = ? AND org_id = ?",
            [$planName, $outputName, $activityName, $orgId]
        );
        
        if ($existing) {
            // Already exists, return it
            return $existing['structure_id'];
        }
        
        // STEP 5: Create new structure
        $structureId = Database::insert('dim_budget_structure', [
            'plan_name' => $planName,
            'output_name' => $outputName,
            'activity_name' => $activityName,
            'item_name' => $itemName,
            'org_id' => $orgId
        ]);
        
        return $structureId;
    }
    
    /**
     * Build hierarchy path by walking up parent_id
     * 
     * @param int $itemId
     * @return array Array of names from L0 (root) to current item
     */
    private static function buildHierarchyPath(int $itemId): array
    {
        $path = [];
        $currentId = $itemId;
        
        // Walk up the tree (max 10 levels to prevent infinite loop)
        for ($i = 0; $i < 10; $i++) {
            $item = Database::queryOne(
                "SELECT id, item_name, parent_id, level FROM budget_category_items WHERE id = ?",
                [$currentId]
            );
            
            if (!$item) break;
            
            // Add to beginning of path (reverse order)
            array_unshift($path, $item['item_name']);
            
            // Move to parent
            if ($item['parent_id']) {
                $currentId = $item['parent_id'];
            } else {
                break; // Reached root
            }
        }
        
        return $path;
    }
}

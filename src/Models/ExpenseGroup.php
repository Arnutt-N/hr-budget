<?php
namespace App\Models;

use App\Core\Model;

class ExpenseGroup extends Model {
    protected $table = 'expense_groups';
    protected $fillable = [
        'expense_type_id', 'code', 'name_th', 'name_en', 'description',
        'sort_order', 'is_active',
        'deleted_at', 'created_by', 'updated_by'
    ];

    /**
     * Get all groups with their items for a specific expense type
     */
    public static function getAllWithItemsByType(int $typeId, ?int $organizationId = null): array
    {
        return self::getAllWithHierarchicalItemsByType($typeId, $organizationId);
    }

    /**
     * Get all groups with their items in a hierarchical tree structure
     * Optional: Filter by Organization ID (based on budget_line_items)
     */
    public static function getAllWithHierarchicalItemsByType(int $typeId, ?int $organizationId = null): array
    {
        $db = \App\Core\Database::getInstance();
        
        // 1. Get groups for this type
        $groups = self::where('expense_type_id', $typeId)
                      ->where('is_active', 1)
                      ->orderBy('sort_order', 'ASC')
                      ->get();
        
        if (empty($groups)) {
            return [];
        }
        
        // 2. Get all items for these groups (Raw Data)
        $groupIds = array_column($groups, 'id');
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        
        $itemsQuery = "SELECT * FROM expense_items 
                       WHERE expense_group_id IN ($placeholders) 
                       AND deleted_at IS NULL 
                       ORDER BY expense_group_id, level, sort_order";
        $allItems = \App\Core\Database::query($itemsQuery, $groupIds);

        // 3. Filter by Organization (if provided)
        if ($organizationId) {
            $allItems = self::filterItemsByOrganization($allItems, $organizationId, $typeId);
        }
        
        // 4. Group items by expense_group_id
        $itemsByGroup = [];
        foreach ($allItems as $item) {
            $groupId = $item['expense_group_id'];
            if (!isset($itemsByGroup[$groupId])) {
                $itemsByGroup[$groupId] = [];
            }
            $itemsByGroup[$groupId][] = $item;
        }
        
        // 5. Build hierarchy and filter empty groups
        $validGroups = [];
        foreach ($groups as &$group) {
            $rawItems = $itemsByGroup[$group['id']] ?? [];
            $group['items'] = self::buildTree($rawItems);
            
            // Only add group if it has items
            if (!empty($group['items'])) {
                $validGroups[] = $group;
            }
        }
        
        return $validGroups;
    }

    private static function filterItemsByOrganization(array $allItems, int $orgId, int $typeId): array
    {
        // Get whitelisted items for this org
        $sql = "SELECT DISTINCT expense_item_id FROM budget_line_items 
                WHERE division_id = ? AND expense_type_id = ?";
        $whitelisted = \App\Core\Database::query($sql, [$orgId, $typeId]);
        $allowedIds = array_column($whitelisted, 'expense_item_id');

        // If no allocation at all, usually return empty
        // BUT strict filtering: return empty array
        if (empty($allowedIds)) return [];

        // Map ID -> Item & Parent
        $itemMap = [];
        foreach ($allItems as $item) {
            $itemMap[$item['id']] = $item;
        }

        // Identify all IDs to keep (Whitelist + Ancestors)
        $keepIds = [];
        foreach ($allowedIds as $id) {
            $currentId = $id;
            while ($currentId && isset($itemMap[$currentId])) {
                $keepIds[$currentId] = true;
                $currentId = $itemMap[$currentId]['parent_id'];
            }
        }

        // Filter valid items
        return array_filter($allItems, function($item) use ($keepIds) {
            return isset($keepIds[$item['id']]);
        });
    }

    private static function buildTree(array $elements, $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                // Check if this item has children in the filtered elements list
                // Recursively build tree
                $children = self::buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}

<?php
namespace App\Models;

use App\Core\Database;

class BudgetCategoryItem
{
    /**
     * Get items by category ID
     */
    public static function getByCategory(int $categoryId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_category_items WHERE category_id = ?";
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, id ASC";
        
        return Database::query($sql, [$categoryId]);
    }

    /**
     * Find item by ID
     */
    public static function find(int $id): ?array
    {
        $result = Database::query("SELECT * FROM budget_category_items WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }
    /**
     * Get children of a given item ID
     */
    public static function getChildren(int $parentId): array
    {
        $sql = "SELECT * FROM budget_category_items WHERE parent_id = ? ORDER BY sort_order ASC, id ASC";
        return Database::query($sql, [$parentId]);
    }

    /**
     * Get the parent of a given item ID
     */
    public static function getParent(int $id): ?array
    {
        $item = self::find($id);
        if (!$item || empty($item['parent_id'])) {
            return null;
        }
        return self::find($item['parent_id']);
    }

    /**
     * Recursively retrieve the full hierarchy starting from specific parent
     * @param int|null $parentId - Parent ID to start from (NULL = root items)
     */
    public static function getHierarchy(?int $parentId = null): array
    {
        $sql = "SELECT * FROM budget_category_items WHERE is_active = 1";
        
        if ($parentId === null) {
            $sql .= " AND parent_id IS NULL";
            $items = Database::query($sql . " ORDER BY sort_order ASC, id ASC");
        } else {
            $sql .= " AND parent_id = ?";
            $items = Database::query($sql . " ORDER BY sort_order ASC, id ASC", [$parentId]);
        }
        
        foreach ($items as &$item) {
            $item['children'] = self::getHierarchy($item['id']);
        }
        
        return $items;
    }
    
    /**
     * Get root items for tab using ExpenseType -> ExpenseGroup -> ExpenseItem structure
     * @param array $explicitItemIds - List of IDs to force include (e.g. already saved items)
     */
    public static function getRootItemsForTab(string $tabName, ?int $organizationId = null, array $explicitItemIds = []): array
    {
        // 1. Map Tab Names to ExpenseType IDs
        $typeMap = [
            'งบบุคลากร' => 1,
            'งบดำเนินงาน' => 2,
            'งบลงทุน' => 3,
            'งบเงินอุดหนุน' => 4,
            'งบรายจ่ายอื่น' => 5
        ];

        // normalize tab name
        $normalizedTabName = trim($tabName);
        foreach ($typeMap as $key => $id) {
            if (strpos($normalizedTabName, $key) !== false) {
                $typeId = $id;
                break;
            }
        }

        if (!isset($typeId)) {
            return [];
        }

        // 2. Fetch Groups and Items using ExpenseGroup model
        // We use getAllWithItemsByType which returns Groups -> Items tree
        // User Request: "งบลงทุน ต้องไม่มีรายการ" => Type 3
        if ($typeId === 3) {
            return []; // Force empty for Investment Budget
        }
        
        // User Request: Strict Hierarchy Filtering (Org -> Plan -> ...)
        // Retrieve "Personnel Plan" ID dynamically
        $db = \App\Core\Database::getInstance();
        $plan = \App\Core\Database::queryOne("SELECT id FROM plans WHERE name_th LIKE ?", ['%บุคลากร%']);
        $planId = $plan['id'] ?? null;

        $groups = \App\Models\ExpenseGroup::getAllWithItemsByType($typeId, $organizationId, $planId);

        // 3. Transform Groups into "Root Items" structure expected by the View
        // View expects: [ {id, name, children: [...]}, ... ]
        $rootItems = [];

        foreach ($groups as $group) {
            $groupId = 'group_' . $group['id'];
            
            // Inject parent_id to children so they link to this group row
            $children = $group['items'] ?? [];
            foreach ($children as &$child) {
                // Top level children of a group should have the group as parent
                if (empty($child['parent_id'])) {
                    $child['parent_id'] = $groupId;
                }
            }
            unset($child); // Break reference

            $rootItem = [
                'id' => $groupId, // Virtual ID for group
                'name' => $group['name_th'],
                'name_th' => $group['name_th'],
                'parent_id' => null,
                'children' => $children, // These are the actual Expense Items
                'is_group' => true, // Flag to identify this is a group wrapper
                // User Request: "แต่ละแทบ รายการหลัก บนสุด ใช้ไอคอน โฟลเดอร์"
                'icon' => 'folder' 
            ];
            $rootItems[] = $rootItem;
        }

        return $rootItems;
    }

    /**
     * Get all items with hierarchy support (for admin)
     * 
     * @param bool $includeInactive Include inactive items
     * @param bool $includeDeleted Include soft-deleted items
     * @return array
     */
    public static function getAll(bool $includeInactive = false, bool $includeDeleted = false): array
    {
        $sql = "SELECT * FROM budget_category_items WHERE 1=1";
        
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY sort_order ASC, level ASC, id ASC";
        
        return Database::query($sql);
    }

    /**
     * Create new item
     * 
     * @param array $data
     * @return int|false Inserted ID or false on failure
     */
    public static function create(array $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO budget_category_items (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $sets[] = "$field = ?";
            $values[] = $value;
        }
        
        if (empty($sets)) return false;
        
        $values[] = $id;
        $sql = "UPDATE budget_category_items SET " . implode(', ', $sets) . " WHERE id = ?";
        
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function softDelete(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET deleted_at = NOW() WHERE id = ?";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function restore(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET deleted_at = NULL WHERE id = ?";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM budget_category_items WHERE id = ?";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function toggleActive(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET is_active = NOT is_active WHERE id = ?";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function updateSortOrder(int $id, int $sortOrder): bool
    {
        $sql = "UPDATE budget_category_items SET sort_order = ? WHERE id = ?";
        $stmt = Database::getInstance()->prepare($sql);
        return $stmt->execute([$sortOrder, $id]);
    }
}

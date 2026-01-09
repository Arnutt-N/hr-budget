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
     * Recursively retrieve the full hierarchy for a category
     */
    public static function getHierarchy(int $categoryId): array
    {
        $rootItems = self::getByCategory($categoryId);
        $build = function ($items) use (&$build) {
            foreach ($items as &$item) {
                $item['children'] = $build(self::getChildren($item['id']));
            }
            return $items;
        };
        return $build($rootItems);
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
        $fields = ['category_id', 'name', 'code', 'parent_id', 'level', 'description', 'sort_order', 'is_active', 'created_by'];
        $values = [];
        $placeholders = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[] = $data[$field];
                $placeholders[] = '?';
            }
        }
        
        $sql = "INSERT INTO budget_category_items (" . implode(', ', array_keys($data)) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        return Database::execute($sql, $values);
    }

    /**
     * Update item
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        $sets = [];
        $values = [];
        
        $allowedFields = ['category_id', 'name', 'code', 'parent_id', 'level', 'description', 'sort_order', 'is_active', 'updated_by'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($sets)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE budget_category_items SET " . implode(', ', $sets) . " WHERE id = ?";
        
        return Database::execute($sql, $values) !== false;
    }

    /**
     * Soft delete item
     * 
     * @param int $id
     * @return bool
     */
    public static function softDelete(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET deleted_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$id]) !== false;
    }

    /**
     * Restore soft-deleted item
     * 
     * @param int $id
     * @return bool
     */
    public static function restore(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET deleted_at = NULL WHERE id = ?";
        return Database::execute($sql, [$id]) !== false;
    }

    /**
     * Permanently delete item
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM budget_category_items WHERE id = ?";
        return Database::execute($sql, [$id]) !== false;
    }

    /**
     * Toggle active status
     * 
     * @param int $id
     * @return bool
     */
    public static function toggleActive(int $id): bool
    {
        $sql = "UPDATE budget_category_items SET is_active = NOT is_active WHERE id = ?";
        return Database::execute($sql, [$id]) !== false;
    }

    /**
     * Update sort order
     * 
     * @param int $id
     * @param int $sortOrder
     * @return bool
     */
    public static function updateSortOrder(int $id, int $sortOrder): bool
    {
        $sql = "UPDATE budget_category_items SET sort_order = ? WHERE id = ?";
        return Database::execute($sql, [$sortOrder, $id]) !== false;
    }
}

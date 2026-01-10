<?php
/**
 * BudgetCategory Model
 * 
 * Handles budget categories (hierarchical)
 */

namespace App\Models;

use App\Core\Database;

class BudgetCategory
{
    /**
     * Get all active categories
     */
    public static function all(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_categories";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, level ASC, id ASC";
        
        return Database::query($sql, $params);
    }

    /**
     * Get categories as tree structure
     */
    public static function getTree(bool $activeOnly = true): array
    {
        $categories = self::all($activeOnly);
        return self::buildTree($categories);
    }

    /**
     * Build tree from flat array
     */
    private static function buildTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = self::buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        
        return $tree;
    }

    /**
     * Find category by ID
     */
    public static function find(int $id): ?array
    {
        $result = Database::query("SELECT * FROM budget_categories WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    /**
     * Find category by code
     */
    public static function findByCode(string $code): ?array
    {
        $result = Database::query("SELECT * FROM budget_categories WHERE code = ?", [$code]);
        return $result[0] ?? null;
    }

    /**
     * Get categories for dropdown/select
     */
    public static function getForSelect(bool $activeOnly = true): array
    {
        $categories = self::all($activeOnly);
        $options = [];
        
        foreach ($categories as $cat) {
            $prefix = str_repeat('— ', (int) $cat['level']);
            $options[] = [
                'id' => $cat['id'],
                'name' => $prefix . $cat['name_th'],
                'name_th' => $cat['name_th'], // Raw name without prefix
                'code' => $cat['code'],
                'level' => $cat['level'],
                'description' => $cat['description'] ?? '',
            ];
        }
        
        return $options;
    }

    /**
     * Create new category
     */
    public static function create(array $data): int
    {
        return Database::insert('budget_categories', [
            'code' => $data['code'],
            'name_th' => $data['name_th'],
            'name_en' => $data['name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'level' => $data['level'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'is_plan' => $data['is_plan'] ?? false,
            'plan_name' => $data['plan_name'] ?? null,
        ]);
    }

    /**
     * Update category
     */
    public static function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowedFields = ['code', 'name_th', 'name_en', 'description', 'parent_id', 'level', 'sort_order', 'is_active', 'is_plan', 'plan_name'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return Database::update('budget_categories', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete category (soft delete by setting is_active = false)
     */
    public static function delete(int $id): bool
    {
        return Database::update('budget_categories', ['is_active' => false], 'id = ?', [$id]) > 0;
    }

    /**
     * Get root category (รายการค่าใช้จ่ายบุคลากรภาครัฐ)
     */
    public static function getRootCategory(): ?array
    {
        $result = Database::query("SELECT * FROM budget_categories WHERE code = ? AND level = 0", ['GOVT_PERSONNEL_EXP']);
        return $result[0] ?? null;
    }

    /**
     * Get top-level categories (direct children of root)
     * Returns categories like งบบุคลากร, งบดำเนินงาน, etc.
     */
    public static function getTopLevelCategories(bool $activeOnly = true): array
    {
        $root = self::getRootCategory();
        if (!$root) {
            // Fallback: get level 1 categories if root doesn't exist
            $sql = "SELECT * FROM budget_categories WHERE level = 1";
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC, id ASC";
            return Database::query($sql);
        }

        $sql = "SELECT * FROM budget_categories WHERE parent_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        
        return Database::query($sql, [$root['id']]);
    }

    /**
     * Get children hierarchy of a category
     * Returns all descendants as nested array
     */
    public static function getChildrenHierarchy(int $parentId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_categories WHERE parent_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        
        $children = Database::query($sql, [$parentId]);
        
        foreach ($children as &$child) {
            $child['children'] = self::getChildrenHierarchy($child['id'], $activeOnly);
        }
        
        return $children;
    }

    /**
     * Get all categories with their items, including hierarchy
     */
    public static function getAllWithItems(): array
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM budget_categories ORDER BY code");
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->query("SELECT * FROM budget_category_items WHERE is_active = 1 ORDER BY category_id, sort_order, id");
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group items by category_id
        $itemsByCategory = [];
        foreach ($items as $item) {
            $itemsByCategory[$item['category_id']][] = $item;
        }

        // Attach items to categories
        foreach ($categories as $i => $category) {
            $categories[$i]['items'] = $itemsByCategory[$category['id']] ?? [];
        }

        return $categories;
    }
}

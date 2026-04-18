<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetCategoryItemRepository
{
    public function findByCategoryId(int $categoryId): array
    {
        return Database::query(
            "SELECT * FROM budget_category_items WHERE category_id = ? AND deleted_at IS NULL ORDER BY sort_order, id",
            [$categoryId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM budget_category_items WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_category_items', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'code', 'parent_id', 'level', 'sort_order',
            'is_active', 'description', 'category_id'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('budget_category_items', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'budget_category_items',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }

    public function restore(int $id): bool
    {
        return Database::update(
            'budget_category_items',
            ['deleted_at' => null],
            'id = ?',
            [$id]
        ) > 0;
    }

    public function hardDelete(int $id): bool
    {
        return Database::delete('budget_category_items', 'id = ?', [$id]) > 0;
    }

    public function deleteByCategoryId(int $categoryId): int
    {
        return Database::delete('budget_category_items', 'category_id = ?', [$categoryId]);
    }

    /** Bulk replace: delete all then re-insert (used within a transaction). */
    public function replaceItemsUnsafe(int $categoryId, array $items): void
    {
        $this->deleteByCategoryId($categoryId);
        foreach ($items as $item) {
            $item['category_id'] = $categoryId;
            $this->insert($item);
        }
    }
}

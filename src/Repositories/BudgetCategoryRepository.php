<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetCategoryRepository
{
    public function findAll(int $limit = 100, int $offset = 0, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY level, sort_order, id LIMIT ? OFFSET ?";
        return Database::query($sql, [$limit, $offset]);
    }

    public function count(bool $activeOnly = true): int
    {
        $sql = "SELECT COUNT(*) as total FROM budget_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $result = Database::query($sql);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM budget_categories WHERE id = ?", [$id]);
    }

    public function findByFiscalCode(string $fiscalCode): ?array
    {
        return Database::queryOne(
            "SELECT * FROM budget_categories WHERE fiscal_code = ?",
            [$fiscalCode]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     *
     * Active-only by default, matching how the category tree presents itself.
     * The level sync walks every child, including soft-deleted ones, so it
     * passes false.
     */
    public function findByParentId(int $parentId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_categories WHERE parent_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        return Database::query($sql, [$parentId]);
    }

    public function getTree(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM budget_categories";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY level, sort_order, id";
        return Database::query($sql);
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_categories', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['code', 'name_th', 'name_en', 'description', 'parent_id',
            'level', 'sort_order', 'is_active', 'is_plan', 'plan_name',
            'fiscal_code', 'expense_cat_code'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('budget_categories', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('budget_categories', 'id = ?', [$id]) > 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetCategoryRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM budget_categories ORDER BY level, sort_order, id LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM budget_categories");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM budget_categories WHERE id = ?", [$id]);
    }

    public function getTree(): array
    {
        return Database::query(
            "SELECT * FROM budget_categories ORDER BY level, sort_order, id"
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_categories', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['code', 'name_th', 'name_en', 'description', 'parent_id',
            'level', 'sort_order', 'is_active', 'is_plan', 'plan_name'];
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

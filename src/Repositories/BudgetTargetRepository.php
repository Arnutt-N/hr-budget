<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetTargetRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM budget_targets ORDER BY fiscal_year DESC, id DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM budget_targets");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM budget_targets WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_targets', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'target_type_id',
            'fiscal_year',
            'quarter',
            'organization_id',
            'category_id',
            'target_percent',
            'target_amount',
            'notes',
        ];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('budget_targets', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('budget_targets', 'id = ?', [$id]) > 0;
    }
}

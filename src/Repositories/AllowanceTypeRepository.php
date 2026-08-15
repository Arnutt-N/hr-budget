<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AllowanceTypeRepository
{
    public function findAll(): array
    {
        return Database::query(
            "SELECT at.*, ei.name_th AS expense_item_name
             FROM allowance_types at
             LEFT JOIN expense_items ei ON ei.id = at.expense_item_id
             WHERE at.deleted_at IS NULL
             ORDER BY at.id"
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT at.*, ei.name_th AS expense_item_name
             FROM allowance_types at
             LEFT JOIN expense_items ei ON ei.id = at.expense_item_id
             WHERE at.id = ? AND at.deleted_at IS NULL",
            [$id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return Database::queryOne(
            "SELECT * FROM allowance_types WHERE code = ? AND deleted_at IS NULL",
            [$code]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('allowance_types', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'name_th', 'short_name', 'expense_item_id', 'scope', 'vacant_eligible',
            'report_scope', 'basis', 'rate_kind', 'budget_basis', 'legal_ref', 'is_active',
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
        return Database::update('allowance_types', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'allowance_types',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

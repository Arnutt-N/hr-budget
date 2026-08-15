<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PositionAllowanceRepository
{
    public function findByPosition(int $positionId): array
    {
        return Database::query(
            "SELECT pa.*, at.short_name, at.name_th AS allowance_name, at.expense_item_id
             FROM position_allowances pa
             LEFT JOIN allowance_types at ON at.id = pa.allowance_type_id
             WHERE pa.position_id = ? AND pa.deleted_at IS NULL
             ORDER BY pa.effective_from DESC",
            [$positionId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM position_allowances WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('position_allowances', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['effective_from', 'effective_to', 'doc_no', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('position_allowances', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'position_allowances',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

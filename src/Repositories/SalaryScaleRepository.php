<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class SalaryScaleRepository
{
    public function findAll(): array
    {
        return Database::query(
            "SELECT * FROM salary_scales
             WHERE deleted_at IS NULL
             ORDER BY employee_category, level_code, effective_from DESC"
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM salary_scales WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /** แถวที่มีผล ณ วันอ้างอิงของ (category, level) ชุดล่าสุด */
    public function findEffective(string $category, string $levelCode, string $referenceDate): ?array
    {
        return Database::queryOne(
            "SELECT * FROM salary_scales
             WHERE employee_category = ? AND level_code = ? AND deleted_at IS NULL
               AND effective_from <= ?
               AND (effective_to IS NULL OR effective_to >= ?)
             ORDER BY effective_from DESC LIMIT 1",
            [$category, $levelCode, $referenceDate, $referenceDate]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('salary_scales', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['level_code', 'min_amount', 'max_amount', 'effective_from', 'effective_to', 'doc_no'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('salary_scales', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'salary_scales',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

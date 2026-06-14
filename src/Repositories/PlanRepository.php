<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PlanRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM plans WHERE deleted_at IS NULL ORDER BY sort_order ASC, name_th ASC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM plans WHERE deleted_at IS NULL");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM plans WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findByCodeYear(string $code, int $fiscalYear): ?array
    {
        return Database::queryOne(
            "SELECT * FROM plans WHERE code = ? AND fiscal_year = ? AND deleted_at IS NULL",
            [$code, $fiscalYear]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('plans', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'budget_type_id',
            'code',
            'name_th',
            'name_en',
            'description',
            'fiscal_year',
            'sort_order',
            'is_active',
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
        return Database::update('plans', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'plans',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class SalaryRaiseRoundRepository
{
    public function findAll(): array
    {
        return Database::query(
            "SELECT srr.*, fy.year AS fiscal_year
             FROM salary_raise_rounds srr
             LEFT JOIN fiscal_years fy ON fy.id = srr.fiscal_year_id
             WHERE srr.deleted_at IS NULL
             ORDER BY srr.effective_date DESC"
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT srr.*, fy.year AS fiscal_year
             FROM salary_raise_rounds srr
             LEFT JOIN fiscal_years fy ON fy.id = srr.fiscal_year_id
             WHERE srr.id = ? AND srr.deleted_at IS NULL",
            [$id]
        );
    }

    public function findByMonthYear(string $roundMonth, int $roundYearBe): ?array
    {
        return Database::queryOne(
            "SELECT * FROM salary_raise_rounds
             WHERE round_month = ? AND round_year_be = ? AND deleted_at IS NULL",
            [$roundMonth, $roundYearBe]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('salary_raise_rounds', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['effective_date', 'fiscal_year_id', 'include_in_budget', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('salary_raise_rounds', $updateData, 'id = ?', [$id]) > 0;
    }
}

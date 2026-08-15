<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PersonnelBudgetPolicyRepository
{
    public function findAll(): array
    {
        return Database::query(
            "SELECT pbp.*, fy.year AS fiscal_year
             FROM personnel_budget_policies pbp
             LEFT JOIN fiscal_years fy ON fy.id = pbp.fiscal_year_id
             WHERE pbp.deleted_at IS NULL
             ORDER BY fy.year DESC"
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT pbp.*, fy.year AS fiscal_year
             FROM personnel_budget_policies pbp
             LEFT JOIN fiscal_years fy ON fy.id = pbp.fiscal_year_id
             WHERE pbp.id = ? AND pbp.deleted_at IS NULL",
            [$id]
        );
    }

    public function findByFiscalYear(int $fiscalYearId): ?array
    {
        return Database::queryOne(
            "SELECT * FROM personnel_budget_policies
             WHERE fiscal_year_id = ? AND deleted_at IS NULL",
            [$fiscalYearId]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('personnel_budget_policies', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['vacancy_rule', 'calc_mode', 'buffer_percent', 'reference_date', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('personnel_budget_policies', $updateData, 'id = ?', [$id]) > 0;
    }
}

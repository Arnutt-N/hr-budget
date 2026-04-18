<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class FiscalYearRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM fiscal_years ORDER BY year DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM fiscal_years");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM fiscal_years WHERE id = ?", [$id]);
    }

    public function findByYear(int $year): ?array
    {
        return Database::queryOne("SELECT * FROM fiscal_years WHERE year = ?", [$year]);
    }

    public function findCurrent(): ?array
    {
        return Database::queryOne("SELECT * FROM fiscal_years WHERE is_current = 1 LIMIT 1");
    }

    public function insert(array $data): int
    {
        return Database::insert('fiscal_years', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['year', 'start_date', 'end_date', 'is_current', 'is_closed'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('fiscal_years', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('fiscal_years', 'id = ?', [$id]) > 0;
    }

    public function clearCurrent(): void
    {
        Database::update('fiscal_years', ['is_current' => 0], 'is_current = 1', []);
    }

    public function setCurrent(int $id): void
    {
        Database::update('fiscal_years', ['is_current' => 1], 'id = ?', [$id]);
    }
}

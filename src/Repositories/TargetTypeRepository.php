<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class TargetTypeRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM target_types ORDER BY sort_order ASC, name_th ASC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM target_types");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM target_types WHERE id = ?", [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return Database::queryOne("SELECT * FROM target_types WHERE code = ?", [$code]);
    }

    public function insert(array $data): int
    {
        return Database::insert('target_types', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['code', 'name_th', 'description', 'is_active', 'sort_order'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('target_types', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('target_types', 'id = ?', [$id]) > 0;
    }
}

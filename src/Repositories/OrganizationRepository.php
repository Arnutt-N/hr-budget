<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class OrganizationRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM organizations WHERE parent_id IS NULL ORDER BY sort_order, id LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM organizations");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM organizations WHERE id = ?", [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return Database::queryOne("SELECT * FROM organizations WHERE code = ?", [$code]);
    }

    /** Flat list for dropdowns */
    public function getForSelect(): array
    {
        return Database::query(
            "SELECT id, code, name_th, abbreviation, org_type, parent_id, level
             FROM organizations WHERE is_active = 1 ORDER BY level, sort_order, id"
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('organizations', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['parent_id', 'code', 'name_th', 'abbreviation', 'budget_allocated',
            'level', 'org_type', 'province_code', 'region', 'contact_phone',
            'contact_email', 'address', 'sort_order', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('organizations', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('organizations', 'id = ?', [$id]) > 0;
    }
}

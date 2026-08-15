<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class SalaryRaiseProgressRepository
{
    public function findByRound(int $roundId): array
    {
        return Database::query(
            "SELECT srp.*, o.name_th AS organization_name
             FROM salary_raise_progress srp
             LEFT JOIN organizations o ON o.id = srp.organization_id
             WHERE srp.round_id = ? AND srp.deleted_at IS NULL
             ORDER BY o.name_th",
            [$roundId]
        );
    }

    public function findByRoundAndOrg(int $roundId, int $organizationId): ?array
    {
        return Database::queryOne(
            "SELECT * FROM salary_raise_progress
             WHERE round_id = ? AND organization_id = ? AND deleted_at IS NULL",
            [$roundId, $organizationId]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('salary_raise_progress', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['status', 'completed_at', 'doc_no'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('salary_raise_progress', $updateData, 'id = ?', [$id]) > 0;
    }

    public function countOrganizations(): int
    {
        $result = Database::query("SELECT COUNT(*) AS total FROM organizations");
        return (int) ($result[0]['total'] ?? 0);
    }
}

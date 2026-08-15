<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PersonnelAllowanceRepository
{
    public function findAll(int $limit, int $offset, array $filters = []): array
    {
        $sql = "SELECT pa.*, at.short_name, at.name_th AS allowance_name, p.pay_no
                FROM personnel_allowances pa
                LEFT JOIN allowance_types at ON at.id = pa.allowance_type_id
                LEFT JOIN positions p ON p.id = pa.position_id
                WHERE pa.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['person_id'])) {
            $sql .= " AND pa.person_id = ?";
            $params[] = (string) $filters['person_id'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND pa.position_id = ?";
            $params[] = (int) $filters['position_id'];
        }
        if (!empty($filters['allowance_type_id'])) {
            $sql .= " AND pa.allowance_type_id = ?";
            $params[] = (int) $filters['allowance_type_id'];
        }

        $sql .= " ORDER BY pa.effective_from DESC, pa.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM personnel_allowances WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['person_id'])) {
            $sql .= " AND person_id = ?";
            $params[] = (string) $filters['person_id'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND position_id = ?";
            $params[] = (int) $filters['position_id'];
        }
        if (!empty($filters['allowance_type_id'])) {
            $sql .= " AND allowance_type_id = ?";
            $params[] = (int) $filters['allowance_type_id'];
        }

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM personnel_allowances WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('personnel_allowances', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['amount', 'effective_from', 'effective_to', 'doc_no', 'doc_date', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('personnel_allowances', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'personnel_allowances',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

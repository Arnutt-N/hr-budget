<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PersonnelAssignmentRepository
{
    public function findAll(int $limit, int $offset, array $filters = []): array
    {
        $sql = "SELECT pas.*, p.pay_no, o.name_th AS serving_organization_name
                FROM personnel_assignments pas
                LEFT JOIN positions p ON p.id = pas.position_id
                LEFT JOIN organizations o ON o.id = pas.serving_organization_id
                WHERE pas.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['person_id'])) {
            $sql .= " AND pas.person_id = ?";
            $params[] = (string) $filters['person_id'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND pas.position_id = ?";
            $params[] = (int) $filters['position_id'];
        }
        if (!empty($filters['serving_organization_id'])) {
            $sql .= " AND pas.serving_organization_id = ?";
            $params[] = (int) $filters['serving_organization_id'];
        }

        $sql .= " ORDER BY pas.effective_from DESC, pas.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM personnel_assignments WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['person_id'])) {
            $sql .= " AND person_id = ?";
            $params[] = (string) $filters['person_id'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND position_id = ?";
            $params[] = (int) $filters['position_id'];
        }
        if (!empty($filters['serving_organization_id'])) {
            $sql .= " AND serving_organization_id = ?";
            $params[] = (int) $filters['serving_organization_id'];
        }

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM personnel_assignments WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('personnel_assignments', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['serving_organization_id', 'effective_from', 'effective_to', 'doc_no', 'doc_date', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('personnel_assignments', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'personnel_assignments',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

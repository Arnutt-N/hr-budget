<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PositionRepository
{
    public function findAll(int $limit, int $offset, array $filters = [], ?string $referenceDate = null): array
    {
        $ref = $referenceDate ?? date('Y-m-d');

        $sql = "SELECT p.*,
                       pv.id AS version_id, pv.pos_no, pv.organization_id, pv.level_code,
                       pv.line_code, pv.base_salary, pv.salary_basis, pv.occupancy,
                       pv.lifecycle, pv.months_counted, pv.approval_status,
                       pv.effective_from, pv.effective_to,
                       o.name_th AS organization_name
                FROM positions p
                LEFT JOIN position_versions pv
                    ON pv.position_id = p.id
                    AND pv.deleted_at IS NULL
                    AND pv.effective_from <= ?
                    AND (pv.effective_to IS NULL OR pv.effective_to >= ?)
                LEFT JOIN organizations o ON o.id = pv.organization_id
                WHERE p.deleted_at IS NULL";

        $params = [$ref, $ref];

        if (!empty($filters['organization_id'])) {
            $sql .= " AND pv.organization_id = ?";
            $params[] = (int) $filters['organization_id'];
        }
        if (!empty($filters['employee_category'])) {
            $sql .= " AND p.employee_category = ?";
            $params[] = (string) $filters['employee_category'];
        }
        if (!empty($filters['occupancy'])) {
            $sql .= " AND pv.occupancy = ?";
            $params[] = (string) $filters['occupancy'];
        }
        if (!empty($filters['approval_status'])) {
            $sql .= " AND pv.approval_status = ?";
            $params[] = (string) $filters['approval_status'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (p.pay_no LIKE ? OR pv.pos_no LIKE ?)";
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY p.pay_no LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    public function count(array $filters = [], ?string $referenceDate = null): int
    {
        $ref = $referenceDate ?? date('Y-m-d');

        $sql = "SELECT COUNT(*) AS total
                FROM positions p
                LEFT JOIN position_versions pv
                    ON pv.position_id = p.id
                    AND pv.deleted_at IS NULL
                    AND pv.effective_from <= ?
                    AND (pv.effective_to IS NULL OR pv.effective_to >= ?)
                WHERE p.deleted_at IS NULL";

        $params = [$ref, $ref];

        if (!empty($filters['organization_id'])) {
            $sql .= " AND pv.organization_id = ?";
            $params[] = (int) $filters['organization_id'];
        }
        if (!empty($filters['employee_category'])) {
            $sql .= " AND p.employee_category = ?";
            $params[] = (string) $filters['employee_category'];
        }
        if (!empty($filters['occupancy'])) {
            $sql .= " AND pv.occupancy = ?";
            $params[] = (string) $filters['occupancy'];
        }
        if (!empty($filters['approval_status'])) {
            $sql .= " AND pv.approval_status = ?";
            $params[] = (string) $filters['approval_status'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (p.pay_no LIKE ? OR pv.pos_no LIKE ?)";
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
        }

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM positions WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findByPayNo(string $payNo): ?array
    {
        return Database::queryOne(
            "SELECT * FROM positions WHERE pay_no = ? AND deleted_at IS NULL",
            [$payNo]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('positions', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['pay_no', 'employee_category', 'created_doc_no', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('positions', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'positions',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

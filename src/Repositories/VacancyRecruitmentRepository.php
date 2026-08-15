<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class VacancyRecruitmentRepository
{
    public function findAll(int $limit, int $offset, array $filters = []): array
    {
        // เอาเวอร์ชันล่าสุด (MAX effective_from) ต่อแบบ derived-table — ไม่ใช้ correlated
        // subquery ใน JOIN (N+1-style) และได้ index-friendly
        $sql = "SELECT vr.*, p.pay_no, pv.pos_no, o.name_th AS organization_name
                FROM vacancy_recruitment vr
                LEFT JOIN positions p ON p.id = vr.position_id
                LEFT JOIN (
                    SELECT pv.position_id, pv.pos_no, pv.organization_id
                    FROM position_versions pv
                    JOIN (
                        SELECT position_id, MAX(effective_from) AS max_from
                        FROM position_versions
                        WHERE deleted_at IS NULL
                        GROUP BY position_id
                    ) latest ON latest.position_id = pv.position_id
                            AND latest.max_from = pv.effective_from
                    WHERE pv.deleted_at IS NULL
                ) pv ON pv.position_id = vr.position_id
                LEFT JOIN organizations o ON o.id = pv.organization_id
                WHERE vr.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['fiscal_year_id'])) {
            $sql .= " AND vr.fiscal_year_id = ?";
            $params[] = (int) $filters['fiscal_year_id'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND vr.type = ?";
            $params[] = (string) $filters['type'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND vr.position_id = ?";
            $params[] = (int) $filters['position_id'];
        }

        $sql .= " ORDER BY vr.doc_date DESC, vr.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM vacancy_recruitment WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['fiscal_year_id'])) {
            $sql .= " AND fiscal_year_id = ?";
            $params[] = (int) $filters['fiscal_year_id'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = (string) $filters['type'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND position_id = ?";
            $params[] = (int) $filters['position_id'];
        }

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM vacancy_recruitment WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('vacancy_recruitment', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['type', 'doc_no', 'doc_date', 'is_active'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('vacancy_recruitment', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'vacancy_recruitment',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

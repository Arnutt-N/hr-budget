<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PositionVersionRepository
{
    public function findByPosition(int $positionId): array
    {
        return Database::query(
            "SELECT * FROM position_versions
             WHERE position_id = ? AND deleted_at IS NULL
             ORDER BY effective_from DESC",
            [$positionId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM position_versions WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findOpenVersion(int $positionId, string $beforeDate): ?array
    {
        return Database::queryOne(
            "SELECT * FROM position_versions
             WHERE position_id = ? AND deleted_at IS NULL
               AND effective_to IS NULL AND effective_from < ?
             ORDER BY effective_from DESC LIMIT 1",
            [$positionId, $beforeDate]
        );
    }

    /**
     * เวอร์ชันที่ช่วงเวลาตัดกับ [from, to] (to = NULL = เปิดไม่มีที่สิ้นสุด)
     * ใช้บังคับกติกา "มีช่วงเวลาทุกอย่าง และช่วง tile กันไม่ทับ"
     */
    public function findIntersecting(int $positionId, string $from, ?string $to, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM position_versions
                WHERE position_id = ? AND deleted_at IS NULL
                  AND (? IS NULL OR effective_from <= ?)
                  AND (effective_to IS NULL OR effective_to >= ?)";
        $params = [$positionId, $to, $to, $from];
        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }
        return Database::queryOne($sql, $params);
    }

    public function insert(array $data): int
    {
        return Database::insert('position_versions', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'pos_no', 'organization_id', 'level_code', 'line_code',
            'base_salary', 'salary_basis', 'salary_pre_raise',
            'occupancy', 'lifecycle', 'months_counted', 'approval_status',
            'effective_from', 'effective_to', 'order_doc_no', 'order_doc_date',
        ];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('position_versions', $updateData, 'id = ?', [$id]) > 0;
    }
}

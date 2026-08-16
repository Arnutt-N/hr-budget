<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AllowanceRateRepository
{
    public function findByType(int $allowanceTypeId): array
    {
        return Database::query(
            "SELECT ar.*, d.short_name AS derives_from_short_name
             FROM allowance_rates ar
             LEFT JOIN allowance_types d ON d.id = ar.derives_from_type_id
             WHERE ar.allowance_type_id = ? AND ar.deleted_at IS NULL
             ORDER BY ar.effective_from DESC, ar.level_code",
            [$allowanceTypeId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM allowance_rates WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /** แถว derived ทั้งหมด — ใช้เดินกราฟตอนกันวงวน */
    public function findDerivedRows(): array
    {
        return Database::query(
            "SELECT id, allowance_type_id, derives_from_type_id
             FROM allowance_rates
             WHERE derives_from_type_id IS NOT NULL AND deleted_at IS NULL"
        );
    }

    public function countActiveByType(int $allowanceTypeId): int
    {
        $result = Database::query(
            "SELECT COUNT(*) AS total FROM allowance_rates
             WHERE allowance_type_id = ? AND deleted_at IS NULL",
            [$allowanceTypeId]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /** มีอัตราตัวอื่นอ้างอิง type นี้อยู่หรือไม่ (ลบแม่แบบ derived = ลูกกลายเป็น 0 เงียบๆ) */
    public function hasDerivedDependents(int $allowanceTypeId): bool
    {
        $result = Database::query(
            "SELECT COUNT(*) AS total FROM allowance_rates
             WHERE derives_from_type_id = ? AND deleted_at IS NULL",
            [$allowanceTypeId]
        );
        return (int) ($result[0]['total'] ?? 0) > 0;
    }

    /**
     * จำนวนลูกที่ resolve อัตราแม่ "ชุด (level, line) นี้" — ลูก level/line NULL (generic)
     * ใช้ได้กับทุกชุดของแม่ ตาม matchRate() ใน PersonnelBudgetService
     */
    public function countDependentsFor(
        int $allowanceTypeId,
        ?string $levelCode,
        ?string $lineCode,
        int $excludeRateId
    ): int {
        $result = Database::query(
            "SELECT COUNT(*) AS total FROM allowance_rates
             WHERE derives_from_type_id = ? AND deleted_at IS NULL AND id <> ?
               AND (level_code IS NULL OR level_code = ?)
               AND (line_code IS NULL OR line_code = ?)",
            [$allowanceTypeId, $excludeRateId, $levelCode, $lineCode]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /** ลูกทั้งหมดที่อ้าง type นี้ (ใช้เมื่อแม่ที่ถูกลบเป็น generic เต็มตัว — ครอบทุกชุด) */
    public function countAllDependents(int $allowanceTypeId, int $excludeRateId): int
    {
        $result = Database::query(
            "SELECT COUNT(*) AS total FROM allowance_rates
             WHERE derives_from_type_id = ? AND deleted_at IS NULL AND id <> ?",
            [$allowanceTypeId, $excludeRateId]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    /** อัตราที่เหลือของ type นี้ที่ครอบชุด (level, line) นี้ (level/line NULL = ครอบทุกค่า) */
    public function countActiveCovering(
        int $allowanceTypeId,
        ?string $levelCode,
        ?string $lineCode,
        int $excludeRateId
    ): int {
        $result = Database::query(
            "SELECT COUNT(*) AS total FROM allowance_rates
             WHERE allowance_type_id = ? AND deleted_at IS NULL AND id <> ?
               AND (level_code IS NULL OR level_code = ?)
               AND (line_code IS NULL OR line_code = ?)",
            [$allowanceTypeId, $excludeRateId, $levelCode, $lineCode]
        );
        return (int) ($result[0]['total'] ?? 0);
    }

    public function insert(array $data): int
    {
        return Database::insert('allowance_rates', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'level_code', 'line_code', 'amount', 'percent',
            'derives_from_type_id', 'fallback_amount',
            'effective_from', 'effective_to', 'doc_no',
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
        return Database::update('allowance_rates', $updateData, 'id = ?', [$id]) > 0;
    }

    public function softDelete(int $id): bool
    {
        return Database::update(
            'allowance_rates',
            ['deleted_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$id]
        ) > 0;
    }
}

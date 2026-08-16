<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateAllowanceRateDto;
use App\Dtos\UpdateAllowanceRateDto;
use App\Repositories\AllowanceRateRepository;
use App\Repositories\AllowanceTypeRepository;

final class AllowanceRateService
{
    private const MAX_DERIVE_DEPTH = 10;

    public function __construct(
        private readonly AllowanceRateRepository $repo = new AllowanceRateRepository(),
        private readonly AllowanceTypeRepository $typeRepo = new AllowanceTypeRepository(),
    ) {}

    public function listByType(int $allowanceTypeId): ?array
    {
        if ($this->typeRepo->findById($allowanceTypeId) === null) {
            return null;
        }
        return $this->repo->findByType($allowanceTypeId);
    }

    /**
     * บันทึกอัตราใหม่ — จุดบังคับ "กันวงวน derives_from" (R1)
     * DB กันไม่ได้ (MySQL ห้าม CHECK บนคอลัมน์ FK · repo ไม่ใช้ trigger)
     * ⇒ ตรวจวงจรตอนบันทึกที่นี่เท่านั้น ตาม spec L608
     */
    public function create(string $role, CreateAllowanceRateDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->typeRepo->findById($dto->allowanceTypeId) === null) {
            return null;
        }

        if ($dto->derivesFromTypeId !== null) {
            if ($this->typeRepo->findById($dto->derivesFromTypeId) === null) {
                return null; // type ต้นทางไม่มีจริง
            }
            if ($this->createsCycle($dto->allowanceTypeId, $dto->derivesFromTypeId)) {
                return null; // วงจร — ปฏิเสธ
            }
        }

        return $this->repo->insert([
            'allowance_type_id' => $dto->allowanceTypeId,
            'level_code' => $dto->levelCode,
            'line_code' => $dto->lineCode,
            'amount' => $dto->amount,
            'percent' => $dto->percent,
            'derives_from_type_id' => $dto->derivesFromTypeId,
            'fallback_amount' => $dto->fallbackAmount,
            'effective_from' => $dto->effectiveFrom,
            'effective_to' => $dto->effectiveTo,
            'doc_no' => $dto->docNo,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdateAllowanceRateDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        // defense-in-depth: controller ตรวจแล้ว แต่ service รับประกันเองด้วย
        if (!empty($dto->validate())) {
            return false;
        }

        $rate = $this->repo->findById($id);
        if ($rate === null) {
            return false;
        }

        if ($dto->derivesFromTypeId !== null) {
            if ($this->typeRepo->findById($dto->derivesFromTypeId) === null) {
                return false;
            }
            if ($this->createsCycle((int) $rate['allowance_type_id'], $dto->derivesFromTypeId, $id)) {
                return false;
            }
        }

        return $this->repo->update($id, $dto->toUpdateData());
    }

    /**
     * ลบอัตรา — บล็อกถ้าการลบทำให้ลูก derived resolve ไม่ได้
     * แก้ debt (level+line-aware): เดิมตรวจแค่ระดับ type/level แต่ลูก resolve แม่
     * ด้วยชุด (level, line) ตาม matchRate() — ลบแม่ที่ line ต่างจากลูกได้ ⇒ ลูก resolve ไม่ได้เงียบๆ
     *
     * ตรรกะ:
     * - ลบ generic เต็มตัว (level+line NULL ครอบทุกชุด): บล็อกถ้ามีลูกที่อ้าง type นี้เลย
     * - ลบแบบเฉพาะ (level/line อย่างน้อยหนึ่งค่า): บล็อกถ้ามีลูกที่ต้องการชุดนี้
     *   และไม่มีอัตราเหลือของ type ที่ครอบชุดนี้
     */
    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $rate = $this->repo->findById($id);
        if ($rate === null) {
            return false;
        }

        $typeId = (int) $rate['allowance_type_id'];
        $level = $rate['level_code'] !== null ? (string) $rate['level_code'] : null;
        $line = $rate['line_code'] !== null ? (string) $rate['line_code'] : null;

        if ($level === null && $line === null) {
            // generic เต็มตัว — ครอบทุกชุด ถ้าเดลลูกที่อ้าง type นั้นจะ resolve ไม่ได้เลย
            if ($this->repo->countAllDependents($typeId, $id) > 0) {
                return false;
            }
        } else {
            if ($this->repo->countDependentsFor($typeId, $level, $line, $id) > 0) {
                if ($this->repo->countActiveCovering($typeId, $level, $line, $id) === 0) {
                    return false; // ชุดนี้ไม่มีอัตราแม่เหลือแล้ว — ลูกจะ resolve ไม่ได้
                }
            }
        }

        return $this->repo->softDelete($id);
    }

    /**
     * เดินกราฟ derives_from_type_id จาก parentTypeId ขึ้นต้น
     * ถ้าวนกลับมาชน childTypeId (หรือลึกเกิน MAX_DERIVE_DEPTH) = วงจร
     * การเปลี่ยนแถวเดิม ($excludeRateId) ไม่นับแถวตัวเองในกราฟ
     */
    public function createsCycle(int $childTypeId, int $parentTypeId, ?int $excludeRateId = null): bool
    {
        if ($childTypeId === $parentTypeId) {
            return true; // self-cycle
        }

        $edges = [];
        foreach ($this->repo->findDerivedRows() as $row) {
            if ($excludeRateId !== null && (int) $row['id'] === $excludeRateId) {
                continue;
            }
            $edges[(int) $row['allowance_type_id']] = (int) $row['derives_from_type_id'];
        }
        // เส้นขอบใหม่ที่กำลังจะเพิ่ม
        $edges[$childTypeId] = $parentTypeId;

        $current = $parentTypeId;
        $depth = 0;
        while (isset($edges[$current])) {
            $current = $edges[$current];
            if ($current === $childTypeId) {
                return true;
            }
            if (++$depth > self::MAX_DERIVE_DEPTH) {
                return true; // ลึกเกินไป = มีวงจรอยู่แล้วในกราฟ
            }
        }

        return false;
    }
}

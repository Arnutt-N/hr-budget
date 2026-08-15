<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateAllowanceRateDto;
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

    public function update(string $role, int $id, array $data): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $rate = $this->repo->findById($id);
        if ($rate === null) {
            return false;
        }

        if (array_key_exists('derives_from_type_id', $data)) {
            $newParent = $data['derives_from_type_id'] === null ? null : (int) $data['derives_from_type_id'];
            if ($newParent !== null) {
                if ($this->typeRepo->findById($newParent) === null) {
                    return false;
                }
                if ($this->createsCycle((int) $rate['allowance_type_id'], $newParent, $id)) {
                    return false;
                }
            }
        }

        if (isset($data['amount']) && (float) $data['amount'] < 0) {
            return false;
        }
        if (isset($data['percent']) && ((float) $data['percent'] < 0 || (float) $data['percent'] > 100)) {
            return false;
        }

        return $this->repo->update($id, $data);
    }

    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if ($this->repo->findById($id) === null) {
            return false;
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

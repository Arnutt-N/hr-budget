<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreatePositionDto;
use App\Dtos\CreatePositionVersionDto;
use App\Dtos\UpdatePositionDto;
use App\Dtos\UpdatePositionVersionDto;
use App\Repositories\PositionRepository;
use App\Repositories\PositionVersionRepository;

final class PositionService
{
    public function __construct(
        private readonly PositionRepository $repo = new PositionRepository(),
        private readonly PositionVersionRepository $versionRepo = new PositionVersionRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page, int $perPage, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->repo->count($filters);
        $data = $this->repo->findAll($perPage, $offset, $filters);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ],
        ];
    }

    public function findById(int $id): ?array
    {
        $position = $this->repo->findById($id);
        if ($position === null) {
            return null;
        }

        $position['versions'] = $this->versionRepo->findByPosition($id);
        return $position;
    }

    /** สร้างอัตรา + เวอร์ชันแรกใน transaction เดียว */
    public function create(string $role, CreatePositionDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->repo->findByPayNo($dto->payNo) !== null) {
            return null; // เลขถือจ่ายซ้ำ
        }

        Database::beginTransaction();
        try {
            $positionId = $this->repo->insert([
                'pay_no' => $dto->payNo,
                'employee_category' => $dto->employeeCategory,
                'created_doc_no' => $dto->createdDocNo,
                'is_active' => 1,
            ]);

            $this->versionRepo->insert([
                'position_id' => $positionId,
                'organization_id' => $dto->organizationId,
                'pos_no' => $dto->posNo,
                'level_code' => $dto->levelCode,
                'line_code' => $dto->lineCode,
                'base_salary' => $dto->baseSalary,
                'salary_basis' => 'estimated', // ค่าเริ่มต้นปลอดภัย — ตามเอกสารออกแบบ
                'occupancy' => $dto->occupancy,
                'lifecycle' => 'active',
                'months_counted' => $dto->monthsCounted,
                'approval_status' => 'approved',
                'effective_from' => $dto->effectiveFrom,
                'effective_to' => null,
                'order_doc_no' => $dto->orderDocNo,
                'order_doc_date' => $dto->orderDocDate,
            ]);

            Database::commit();
            return $positionId;
        } catch (\Throwable $e) {
            Database::rollback();
            return null;
        }
    }

    public function update(string $role, int $id, UpdatePositionDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if ($this->repo->findById($id) === null) {
            return false;
        }

        if ($dto->payNo !== null) {
            $existing = $this->repo->findByPayNo($dto->payNo);
            if ($existing !== null && (int) $existing['id'] !== $id) {
                return false; // เลขถือจ่ายซ้ำกับอัตราอื่น
            }
        }

        $updateData = [];
        if ($dto->payNo !== null) {
            $updateData['pay_no'] = trim($dto->payNo);
        }
        if ($dto->employeeCategory !== null) {
            $updateData['employee_category'] = $dto->employeeCategory;
        }
        if ($dto->createdDocNo !== null) {
            $updateData['created_doc_no'] = $dto->createdDocNo;
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->repo->update($id, $updateData);
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

    public function listVersions(int $positionId): ?array
    {
        if ($this->repo->findById($positionId) === null) {
            return null;
        }

        return $this->versionRepo->findByPosition($positionId);
    }

    /**
     * เพิ่มเวอร์ชันใหม่ — บังคับช่วงเวลา tile กันไม่ทับ:
     * (1) ปิดเวอร์ชันเดิมที่ยังเปิดอยู่ (effective_to = วันก่อนเริ่มเวอร์ชันใหม่)
     * (2) ตรวจช่วงใหม่ไม่ตัดกับเวอร์ชันปิดแล้วอื่นใด (แก้ช่องโหว่แทรกย้อนหลังซ้อนกัน)
     */
    public function createVersion(string $role, int $positionId, CreatePositionVersionDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->repo->findById($positionId) === null) {
            return null;
        }

        Database::beginTransaction();
        try {
            // เวอร์ชันเปิดที่จะถูกปิดอัตโนมัติ — ไม่นับเป็นตัวตัดช่วง (หลังปิดแล้ว to = วันก่อนเริ่มใหม่)
            $open = $this->versionRepo->findOpenVersion($positionId, $dto->effectiveFrom);
            $excludeId = $open !== null ? (int) $open['id'] : null;

            $conflict = $this->versionRepo->findIntersecting(
                $positionId,
                $dto->effectiveFrom,
                $dto->effectiveTo,
                $excludeId
            );
            if ($conflict !== null) {
                Database::rollback();
                return null; // ช่วงซ้อนกับเวอร์ชันที่มีอยู่
            }

            if ($open !== null) {
                $closeDate = date('Y-m-d', strtotime($dto->effectiveFrom . ' -1 day'));
                $this->versionRepo->update((int) $open['id'], ['effective_to' => $closeDate]);
            }

            $id = $this->versionRepo->insert([
                'position_id' => $positionId,
                'organization_id' => $dto->organizationId,
                'pos_no' => $dto->posNo,
                'level_code' => $dto->levelCode,
                'line_code' => $dto->lineCode,
                'base_salary' => $dto->baseSalary,
                'salary_basis' => $dto->salaryBasis,
                'salary_pre_raise' => $dto->salaryPreRaise,
                'occupancy' => $dto->occupancy,
                'lifecycle' => $dto->lifecycle,
                'months_counted' => $dto->monthsCounted,
                'approval_status' => $dto->approvalStatus,
                'effective_from' => $dto->effectiveFrom,
                'effective_to' => $dto->effectiveTo,
                'order_doc_no' => $dto->orderDocNo,
                'order_doc_date' => $dto->orderDocDate,
            ]);

            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollback();
            return null;
        }
    }

    public function updateVersion(string $role, int $positionId, int $versionId, UpdatePositionVersionDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        // defense-in-depth: controller ตรวจแล้ว แต่ service รับประกันเองด้วย
        // (caller ตรง เช่น เทสต์/job ในอนาคต ต้องไม่หลุดค่าไม่ valid)
        if (!empty($dto->validate())) {
            return false;
        }

        $version = $this->versionRepo->findById($versionId);
        if ($version === null || (int) $version['position_id'] !== $positionId) {
            return false;
        }

        // ถ้าขยับช่วงเวลา ต้องตรวจว่าไม่ไปตัดกับเวอร์ชันอื่น
        $resolvedFrom = $dto->effectiveFrom ?? $version['effective_from'];
        $resolvedTo = $dto->effectiveTo ?? $version['effective_to'];
        if ($dto->effectiveFrom !== null || $dto->effectiveTo !== null) {
            $conflict = $this->versionRepo->findIntersecting(
                $positionId,
                $resolvedFrom,
                $resolvedTo,
                $versionId
            );
            if ($conflict !== null) {
                return false;
            }
            if ($resolvedTo !== null && $resolvedTo < $resolvedFrom) {
                return false;
            }
        }

        return $this->versionRepo->update($versionId, $dto->toUpdateData());
    }
}

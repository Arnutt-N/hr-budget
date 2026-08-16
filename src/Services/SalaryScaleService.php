<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateSalaryScaleDto;
use App\Dtos\UpdateSalaryScaleDto;
use App\Repositories\SalaryScaleRepository;

final class SalaryScaleService
{
    public function __construct(
        private readonly SalaryScaleRepository $repo = new SalaryScaleRepository(),
    ) {}

    public function list(): array
    {
        return $this->repo->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function create(string $role, CreateSalaryScaleDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        // กันซ้อนช่วงเวลา: (category, level) เดียวกัน ช่วงใหม่ต้องไม่ทับช่วงที่ยังมีผล
        $overlap = $this->repo->findEffective(
            $dto->employeeCategory,
            $dto->levelCode,
            $dto->effectiveFrom
        );
        if ($overlap !== null && $overlap['effective_to'] === null) {
            return null; // มีช่วงเปิดอยู่แล้ว — ต้องปิดก่อน (หรือให้ effective_to ของใหม่)
        }

        return $this->repo->insert([
            'employee_category' => $dto->employeeCategory,
            'level_code' => $dto->levelCode,
            'min_amount' => $dto->minAmount,
            'max_amount' => $dto->maxAmount,
            'effective_from' => $dto->effectiveFrom,
            'effective_to' => $dto->effectiveTo,
            'doc_no' => $dto->docNo,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdateSalaryScaleDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        // defense-in-depth: controller ตรวจแล้ว แต่ service รับประกันเองด้วย
        if (!empty($dto->validate())) {
            return false;
        }

        $scale = $this->repo->findById($id);
        if ($scale === null) {
            return false;
        }

        // ตรวจ min<=max แบบรวมค่าเดิม (แก้เฉพาะข้างเดียวก็ต้องยังสอดคล้อง)
        $min = $dto->minAmount ?? (float) $scale['min_amount'];
        $max = $dto->maxAmount ?? (float) $scale['max_amount'];
        if ($min < 0 || $max < $min) {
            return false;
        }

        $updateData = $dto->toUpdateData();
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
}

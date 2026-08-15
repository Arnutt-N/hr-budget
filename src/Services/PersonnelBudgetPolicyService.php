<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreatePersonnelBudgetPolicyDto;
use App\Dtos\UpdatePersonnelBudgetPolicyDto;
use App\Repositories\PersonnelBudgetPolicyRepository;

final class PersonnelBudgetPolicyService
{
    public function __construct(
        private readonly PersonnelBudgetPolicyRepository $repo = new PersonnelBudgetPolicyRepository(),
    ) {}

    public function list(): array
    {
        return $this->repo->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    /** หนึ่งแถวต่อปีงบ — สร้างซ้ำปีเดิมต้องปฏิเสธ */
    public function create(string $role, CreatePersonnelBudgetPolicyDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        $fy = Database::queryOne("SELECT id FROM fiscal_years WHERE id = ?", [$dto->fiscalYearId]);
        if ($fy === null) {
            return null;
        }
        if ($this->repo->findByFiscalYear($dto->fiscalYearId) !== null) {
            return null;
        }

        return $this->repo->insert([
            'fiscal_year_id' => $dto->fiscalYearId,
            'vacancy_rule' => $dto->vacancyRule,
            'calc_mode' => $dto->calcMode,
            'buffer_percent' => $dto->bufferPercent,
            'reference_date' => $dto->referenceDate,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdatePersonnelBudgetPolicyDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }
        if ($this->repo->findById($id) === null) {
            return false;
        }

        $updateData = [];
        if ($dto->vacancyRule !== null) {
            $updateData['vacancy_rule'] = $dto->vacancyRule;
        }
        if ($dto->calcMode !== null) {
            $updateData['calc_mode'] = $dto->calcMode;
        }
        if ($dto->bufferPercent !== null) {
            $updateData['buffer_percent'] = $dto->bufferPercent;
        }
        if ($dto->referenceDate !== null) {
            $updateData['reference_date'] = $dto->referenceDate;
        }

        if (empty($updateData)) {
            return true;
        }
        return $this->repo->update($id, $updateData);
    }
}

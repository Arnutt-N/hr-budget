<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateAllowanceTypeDto;
use App\Dtos\UpdateAllowanceTypeDto;
use App\Repositories\AllowanceTypeRepository;

final class AllowanceTypeService
{
    public function __construct(
        private readonly AllowanceTypeRepository $repo = new AllowanceTypeRepository(),
    ) {}

    public function list(): array
    {
        return $this->repo->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function create(string $role, CreateAllowanceTypeDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        if (!empty($dto->validate())) {
            return null;
        }
        if ($this->repo->findByCode($dto->code) !== null) {
            return null; // รหัสซ้ำ
        }

        $reportScope = implode(',', $dto->reportScope);
        return $this->repo->insert([
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'short_name' => $dto->shortName,
            'expense_item_id' => $dto->expenseItemId,
            'scope' => $dto->scope,
            'vacant_eligible' => $dto->vacantEligible ? 1 : 0,
            'report_scope' => $reportScope !== '' ? $reportScope : 'personnel',
            'basis' => $dto->basis,
            'rate_kind' => $dto->rateKind,
            'budget_basis' => $dto->budgetBasis,
            'legal_ref' => $dto->legalRef,
            'is_active' => 1,
        ]);
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

    public function update(string $role, int $id, UpdateAllowanceTypeDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if ($this->repo->findById($id) === null) {
            return false;
        }

        $updateData = [];
        if ($dto->nameTh !== null) {
            $updateData['name_th'] = $dto->nameTh;
        }
        if ($dto->shortName !== null) {
            $updateData['short_name'] = $dto->shortName;
        }
        if ($dto->expenseItemId !== null) {
            $updateData['expense_item_id'] = $dto->expenseItemId;
        }
        if ($dto->scope !== null) {
            $updateData['scope'] = $dto->scope;
        }
        if ($dto->vacantEligible !== null) {
            $updateData['vacant_eligible'] = $dto->vacantEligible ? 1 : 0;
        }
        if ($dto->reportScope !== null) {
            $updateData['report_scope'] = implode(',', $dto->reportScope);
        }
        if ($dto->basis !== null) {
            $updateData['basis'] = $dto->basis;
        }
        if ($dto->rateKind !== null) {
            $updateData['rate_kind'] = $dto->rateKind;
        }
        if ($dto->budgetBasis !== null) {
            $updateData['budget_basis'] = $dto->budgetBasis;
        }
        if ($dto->legalRef !== null) {
            $updateData['legal_ref'] = $dto->legalRef;
        }
        if ($dto->isActive !== null) {
            $updateData['is_active'] = $dto->isActive ? 1 : 0;
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->repo->update($id, $updateData);
    }
}

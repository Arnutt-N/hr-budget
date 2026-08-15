<?php

declare(strict_types=1);

namespace App\Services;

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

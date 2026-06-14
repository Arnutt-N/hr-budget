<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateBudgetTargetDto;
use App\Dtos\UpdateBudgetTargetDto;
use App\Repositories\BudgetTargetRepository;

final class BudgetTargetService
{
    public function __construct(
        private readonly BudgetTargetRepository $repo = new BudgetTargetRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->repo->count();
        $data = $this->repo->findAll($perPage, $offset);

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
        return $this->repo->findById($id);
    }

    public function create(string $role, CreateBudgetTargetDto $dto, ?int $actorId = null): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        try {
            return $this->repo->insert([
                'target_type_id' => $dto->targetTypeId,
                'fiscal_year' => $dto->fiscalYear,
                'quarter' => $dto->quarter,
                'organization_id' => $dto->organizationId,
                'category_id' => $dto->categoryId,
                'target_percent' => $dto->targetPercent,
                'target_amount' => $dto->targetAmount,
                'notes' => $dto->notes,
                // From the authenticated user, never from client input.
                'created_by' => $actorId,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function update(string $role, int $id, UpdateBudgetTargetDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        $updateData = [];
        if ($dto->targetTypeId !== null) {
            $updateData['target_type_id'] = $dto->targetTypeId;
        }
        if ($dto->fiscalYear !== null) {
            $updateData['fiscal_year'] = $dto->fiscalYear;
        }
        if ($dto->quarter !== null) {
            $updateData['quarter'] = $dto->quarter;
        }
        if ($dto->organizationId !== null) {
            $updateData['organization_id'] = $dto->organizationId;
        }
        if ($dto->categoryId !== null) {
            $updateData['category_id'] = $dto->categoryId;
        }
        if ($dto->targetPercent !== null) {
            $updateData['target_percent'] = $dto->targetPercent;
        }
        if ($dto->targetAmount !== null) {
            $updateData['target_amount'] = $dto->targetAmount;
        }
        if ($dto->notes !== null) {
            $updateData['notes'] = $dto->notes;
        }

        if (!empty($updateData)) {
            return $this->repo->update($id, $updateData);
        }

        return true;
    }

    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        return $this->repo->delete($id);
    }
}

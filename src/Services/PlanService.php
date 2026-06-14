<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreatePlanDto;
use App\Dtos\UpdatePlanDto;
use App\Repositories\PlanRepository;

final class PlanService
{
    public function __construct(
        private readonly PlanRepository $repo = new PlanRepository(),
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

    public function create(string $role, CreatePlanDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($dto->code !== null && $dto->code !== '') {
            $existing = $this->repo->findByCodeYear($dto->code, $dto->fiscalYear);
            if ($existing !== null) {
                return null;
            }
        }

        return $this->repo->insert([
            'budget_type_id' => $dto->budgetTypeId,
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'name_en' => $dto->nameEn,
            'description' => $dto->description,
            'fiscal_year' => $dto->fiscalYear,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive ? 1 : 0,
        ]);
    }

    public function update(string $role, int $id, UpdatePlanDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        // Guard the code+fiscal_year unique key when either side changes,
        // so a collision surfaces as a clean 422 rather than a swallowed
        // MySQL duplicate-entry error from Database::update().
        $newCode = $dto->code ?? ($existing['code'] ?? null);
        $newYear = $dto->fiscalYear ?? (int) $existing['fiscal_year'];
        if (($dto->code !== null || $dto->fiscalYear !== null) && $newCode !== null && $newCode !== '') {
            $byCode = $this->repo->findByCodeYear($newCode, $newYear);
            if ($byCode !== null && (int) $byCode['id'] !== $id) {
                return false;
            }
        }

        $updateData = [];
        if ($dto->budgetTypeId !== null) {
            $updateData['budget_type_id'] = $dto->budgetTypeId;
        }
        if ($dto->code !== null) {
            $updateData['code'] = $dto->code;
        }
        if ($dto->nameTh !== null) {
            $updateData['name_th'] = $dto->nameTh;
        }
        if ($dto->nameEn !== null) {
            $updateData['name_en'] = $dto->nameEn;
        }
        if ($dto->description !== null) {
            $updateData['description'] = $dto->description;
        }
        if ($dto->fiscalYear !== null) {
            $updateData['fiscal_year'] = $dto->fiscalYear;
        }
        if ($dto->sortOrder !== null) {
            $updateData['sort_order'] = $dto->sortOrder;
        }
        if ($dto->isActive !== null) {
            $updateData['is_active'] = $dto->isActive ? 1 : 0;
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

        return $this->repo->softDelete($id);
    }
}

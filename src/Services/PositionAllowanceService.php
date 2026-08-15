<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreatePositionAllowanceDto;
use App\Dtos\UpdatePositionAllowanceDto;
use App\Repositories\AllowanceTypeRepository;
use App\Repositories\PositionAllowanceRepository;
use App\Repositories\PositionRepository;

final class PositionAllowanceService
{
    public function __construct(
        private readonly PositionAllowanceRepository $repo = new PositionAllowanceRepository(),
        private readonly PositionRepository $positionRepo = new PositionRepository(),
        private readonly AllowanceTypeRepository $typeRepo = new AllowanceTypeRepository(),
    ) {}

    public function listByPosition(int $positionId): ?array
    {
        if ($this->positionRepo->findById($positionId) === null) {
            return null;
        }
        return $this->repo->findByPosition($positionId);
    }

    public function create(string $role, CreatePositionAllowanceDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        if ($this->positionRepo->findById($dto->positionId) === null) {
            return null;
        }
        if ($this->typeRepo->findById($dto->allowanceTypeId) === null) {
            return null;
        }

        return $this->repo->insert([
            'position_id' => $dto->positionId,
            'allowance_type_id' => $dto->allowanceTypeId,
            'effective_from' => $dto->effectiveFrom,
            'effective_to' => $dto->effectiveTo,
            'doc_no' => $dto->docNo,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdatePositionAllowanceDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }
        if (!empty($dto->validate())) {
            return false;
        }
        if ($this->repo->findById($id) === null) {
            return false;
        }
        return $this->repo->update($id, $dto->toUpdateData());
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

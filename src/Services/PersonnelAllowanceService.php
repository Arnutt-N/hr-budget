<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreatePersonnelAllowanceDto;
use App\Dtos\UpdatePersonnelAllowanceDto;
use App\Repositories\AllowanceTypeRepository;
use App\Repositories\PersonnelAllowanceRepository;
use App\Repositories\PositionRepository;

final class PersonnelAllowanceService
{
    public function __construct(
        private readonly PersonnelAllowanceRepository $repo = new PersonnelAllowanceRepository(),
        private readonly PositionRepository $positionRepo = new PositionRepository(),
        private readonly AllowanceTypeRepository $typeRepo = new AllowanceTypeRepository(),
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

    public function create(string $role, CreatePersonnelAllowanceDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        if (!empty($dto->validate())) {
            return null;
        }
        if ($this->positionRepo->findById($dto->positionId) === null) {
            return null;
        }
        if ($this->typeRepo->findById($dto->allowanceTypeId) === null) {
            return null;
        }

        return $this->repo->insert([
            'person_id' => $dto->personId,
            'position_id' => $dto->positionId,
            'allowance_type_id' => $dto->allowanceTypeId,
            'amount' => $dto->amount,
            'effective_from' => $dto->effectiveFrom,
            'effective_to' => $dto->effectiveTo,
            'doc_no' => $dto->docNo,
            'doc_date' => $dto->docDate,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdatePersonnelAllowanceDto $dto): bool
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

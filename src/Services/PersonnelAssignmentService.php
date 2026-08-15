<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreatePersonnelAssignmentDto;
use App\Dtos\UpdatePersonnelAssignmentDto;
use App\Repositories\PersonnelAssignmentRepository;
use App\Repositories\PositionRepository;

final class PersonnelAssignmentService
{
    public function __construct(
        private readonly PersonnelAssignmentRepository $repo = new PersonnelAssignmentRepository(),
        private readonly PositionRepository $positionRepo = new PositionRepository(),
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

    public function create(string $role, CreatePersonnelAssignmentDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        if ($this->positionRepo->findById($dto->positionId) === null) {
            return null;
        }
        $org = \App\Core\Database::queryOne(
            "SELECT id FROM organizations WHERE id = ?",
            [$dto->servingOrganizationId]
        );
        if ($org === null) {
            return null;
        }

        return $this->repo->insert([
            'person_id' => $dto->personId,
            'position_id' => $dto->positionId,
            'serving_organization_id' => $dto->servingOrganizationId,
            'effective_from' => $dto->effectiveFrom,
            'effective_to' => $dto->effectiveTo,
            'doc_no' => $dto->docNo,
            'doc_date' => $dto->docDate,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdatePersonnelAssignmentDto $dto): bool
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

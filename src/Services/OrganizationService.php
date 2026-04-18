<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateOrganizationDto;
use App\Dtos\UpdateOrganizationDto;
use App\Repositories\OrganizationRepository;

final class OrganizationService
{
    public function __construct(
        private readonly OrganizationRepository $repo = new OrganizationRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page = 1, int $perPage = 100): array
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

    /** @return array[] Flat list for dropdowns */
    public function getForSelect(): array
    {
        return $this->repo->getForSelect();
    }

    public function create(string $role, CreateOrganizationDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        $existing = $this->repo->findByCode($dto->code);
        if ($existing !== null) {
            return null;
        }

        $level = 0;
        if ($dto->parentId !== null) {
            $parent = $this->repo->findById($dto->parentId);
            if ($parent === null) {
                return null;
            }
            $level = (int) $parent['level'] + 1;
        }

        return $this->repo->insert([
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'abbreviation' => $dto->abbreviation,
            'org_type' => $dto->orgType,
            'region' => $dto->region,
            'parent_id' => $dto->parentId,
            'level' => $level,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdateOrganizationDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        $updateData = [];
        if ($dto->code !== null) {
            $updateData['code'] = $dto->code;
        }
        if ($dto->nameTh !== null) {
            $updateData['name_th'] = $dto->nameTh;
        }
        if ($dto->abbreviation !== null) {
            $updateData['abbreviation'] = $dto->abbreviation;
        }
        if ($dto->orgType !== null) {
            $updateData['org_type'] = $dto->orgType;
        }
        if ($dto->region !== null) {
            $updateData['region'] = $dto->region;
        }
        if ($dto->parentId !== null) {
            $updateData['parent_id'] = $dto->parentId;
            $parent = $this->repo->findById($dto->parentId);
            $updateData['level'] = $parent !== null ? (int) $parent['level'] + 1 : 0;
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

        return $this->repo->delete($id);
    }
}

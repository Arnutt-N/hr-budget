<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateTargetTypeDto;
use App\Dtos\UpdateTargetTypeDto;
use App\Repositories\TargetTypeRepository;

final class TargetTypeService
{
    public function __construct(
        private readonly TargetTypeRepository $repo = new TargetTypeRepository(),
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

    public function create(string $role, CreateTargetTypeDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        $existing = $this->repo->findByCode($dto->code);
        if ($existing !== null) {
            return null;
        }

        return $this->repo->insert([
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'description' => $dto->description,
            'is_active' => $dto->isActive ? 1 : 0,
            'sort_order' => $dto->sortOrder,
        ]);
    }

    public function update(string $role, int $id, UpdateTargetTypeDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        if ($dto->code !== null) {
            $byCode = $this->repo->findByCode($dto->code);
            if ($byCode !== null && (int) $byCode['id'] !== $id) {
                return false;
            }
        }

        $updateData = [];
        if ($dto->code !== null) {
            $updateData['code'] = $dto->code;
        }
        if ($dto->nameTh !== null) {
            $updateData['name_th'] = $dto->nameTh;
        }
        if ($dto->description !== null) {
            $updateData['description'] = $dto->description;
        }
        if ($dto->isActive !== null) {
            $updateData['is_active'] = $dto->isActive ? 1 : 0;
        }
        if ($dto->sortOrder !== null) {
            $updateData['sort_order'] = $dto->sortOrder;
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

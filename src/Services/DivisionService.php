<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateDivisionDto;
use App\Dtos\UpdateDivisionDto;
use App\Repositories\DivisionRepository;

final class DivisionService
{
    public function __construct(
        private readonly DivisionRepository $repo = new DivisionRepository(),
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

    public function create(string $role, CreateDivisionDto $dto): ?int
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
            'name_en' => $dto->nameEn,
            'short_name' => $dto->shortName,
            'parent_id' => $dto->parentId,
            'type' => $dto->type,
            'is_active' => $dto->isActive ? 1 : 0,
            'sort_order' => $dto->sortOrder,
        ]);
    }

    public function update(string $role, int $id, UpdateDivisionDto $dto): bool
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
        if ($dto->nameEn !== null) {
            $updateData['name_en'] = $dto->nameEn;
        }
        if ($dto->shortName !== null) {
            $updateData['short_name'] = $dto->shortName;
        }
        if ($dto->parentId !== null) {
            $updateData['parent_id'] = $dto->parentId;
        }
        if ($dto->type !== null) {
            $updateData['type'] = $dto->type;
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

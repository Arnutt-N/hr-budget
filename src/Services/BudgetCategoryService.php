<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateCategoryDto;
use App\Dtos\CreateCategoryItemDto;
use App\Dtos\UpdateCategoryDto;
use App\Dtos\UpdateCategoryItemDto;
use App\Repositories\BudgetCategoryItemRepository;
use App\Repositories\BudgetCategoryRepository;

final class BudgetCategoryService
{
    public function __construct(
        private readonly BudgetCategoryRepository $catRepo = new BudgetCategoryRepository(),
        private readonly BudgetCategoryItemRepository $itemRepo = new BudgetCategoryItemRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page = 1, int $perPage = 100): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->catRepo->count();
        $data = $this->catRepo->findAll($perPage, $offset);

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

    public function getTree(): array
    {
        return $this->catRepo->getTree();
    }

    public function findById(int $id): ?array
    {
        $category = $this->catRepo->findById($id);
        if ($category === null) {
            return null;
        }
        $category['items'] = $this->itemRepo->findByCategoryId($id);
        return $category;
    }

    public function create(string $role, CreateCategoryDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        $level = 0;
        if ($dto->parentId !== null) {
            $parent = $this->catRepo->findById($dto->parentId);
            if ($parent === null) {
                return null;
            }
            $level = (int) $parent['level'] + 1;
        }

        return $this->catRepo->insert([
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'name_en' => $dto->nameEn,
            'description' => $dto->description,
            'parent_id' => $dto->parentId,
            'level' => $level,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive ? 1 : 0,
        ]);
    }

    public function update(string $role, int $id, UpdateCategoryDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->catRepo->findById($id);
        if ($existing === null) {
            return false;
        }

        $updateData = [];
        if ($dto->code !== null) $updateData['code'] = $dto->code;
        if ($dto->nameTh !== null) $updateData['name_th'] = $dto->nameTh;
        if ($dto->nameEn !== null) $updateData['name_en'] = $dto->nameEn;
        if ($dto->description !== null) $updateData['description'] = $dto->description;
        if ($dto->sortOrder !== null) $updateData['sort_order'] = $dto->sortOrder;
        if ($dto->isActive !== null) $updateData['is_active'] = $dto->isActive ? 1 : 0;

        if ($dto->parentId !== null) {
            $updateData['parent_id'] = $dto->parentId;
            $parent = $this->catRepo->findById($dto->parentId);
            $updateData['level'] = $parent !== null ? (int) $parent['level'] + 1 : 0;
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->catRepo->update($id, $updateData);
    }

    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        Database::beginTransaction();
        try {
            $this->itemRepo->deleteByCategoryId($id);
            $this->catRepo->delete($id);
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }
    }

    // --- Category Items ---

    /** @return array[] */
    public function listItems(int $categoryId): array
    {
        return $this->itemRepo->findByCategoryId($categoryId);
    }

    public function createItem(string $role, int $categoryId, CreateCategoryItemDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        $category = $this->catRepo->findById($categoryId);
        if ($category === null) {
            return null;
        }

        return $this->itemRepo->insert([
            'category_id' => $categoryId,
            'name' => $dto->name,
            'code' => $dto->code,
            'parent_id' => $dto->parentId,
            'level' => $dto->level,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive ? 1 : 0,
            'description' => $dto->description,
        ]);
    }

    public function updateItem(string $role, int $itemId, UpdateCategoryItemDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->itemRepo->findById($itemId);
        if ($existing === null) {
            return false;
        }

        $updateData = [];
        if ($dto->name !== null) $updateData['name'] = $dto->name;
        if ($dto->code !== null) $updateData['code'] = $dto->code;
        if ($dto->parentId !== null) $updateData['parent_id'] = $dto->parentId;
        if ($dto->level !== null) $updateData['level'] = $dto->level;
        if ($dto->sortOrder !== null) $updateData['sort_order'] = $dto->sortOrder;
        if ($dto->isActive !== null) $updateData['is_active'] = $dto->isActive ? 1 : 0;
        if ($dto->description !== null) $updateData['description'] = $dto->description;

        if (empty($updateData)) {
            return true;
        }

        return $this->itemRepo->update($itemId, $updateData);
    }

    public function deleteItem(string $role, int $itemId): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        return $this->itemRepo->softDelete($itemId);
    }

    public function restoreItem(string $role, int $itemId): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        return $this->itemRepo->restore($itemId);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dtos\UpdateCategoryDto;
use App\Repositories\BudgetCategoryItemRepository;
use App\Repositories\BudgetCategoryRepository;
use App\Services\BudgetCategoryService;
use PHPUnit\Framework\TestCase;

final class BudgetCategoryServiceTest extends TestCase
{
    public function testUpdateRejectsSelfParent(): void
    {
        $catRepo = $this->createMock(BudgetCategoryRepository::class);
        $catRepo->method('findById')->willReturn(['id' => 10, 'parent_id' => null, 'level' => 0]);
        $catRepo->expects($this->never())->method('update');

        $itemRepo = $this->createMock(BudgetCategoryItemRepository::class);
        $service = new BudgetCategoryService($catRepo, $itemRepo);

        $this->assertFalse($service->update('admin', 10, new UpdateCategoryDto(parentId: 10)));
    }

    public function testUpdateRejectsAncestorCycle(): void
    {
        $catRepo = $this->createMock(BudgetCategoryRepository::class);
        // Hierarchy: 10 -> 20 -> 30; trying to set 10's parent_id = 30 creates a cycle.
        $catRepo->method('findById')->willReturnMap([
            [10, ['id' => 10, 'parent_id' => null, 'level' => 0]],
            [30, ['id' => 30, 'parent_id' => 20, 'level' => 2]],
            [20, ['id' => 20, 'parent_id' => 10, 'level' => 1]],
        ]);
        $catRepo->expects($this->never())->method('update');

        $itemRepo = $this->createMock(BudgetCategoryItemRepository::class);
        $service = new BudgetCategoryService($catRepo, $itemRepo);

        $this->assertFalse($service->update('admin', 10, new UpdateCategoryDto(parentId: 30)));
    }

    public function testDeleteSoftDeletesItemsAndDeactivatesCategory(): void
    {
        $catRepo = $this->createMock(BudgetCategoryRepository::class);
        $catRepo->method('findById')->with(10)->willReturn(['id' => 10, 'is_active' => 1]);
        $catRepo->expects($this->never())->method('delete');
        $catRepo->expects($this->once())->method('update')->with(10, ['is_active' => 0])->willReturn(true);

        $itemRepo = $this->createMock(BudgetCategoryItemRepository::class);
        $itemRepo->expects($this->never())->method('deleteByCategoryId');
        $itemRepo->expects($this->once())->method('softDeleteByCategoryId')->with(10)->willReturn(2);

        $service = new BudgetCategoryService($catRepo, $itemRepo);

        $this->assertTrue($service->delete('admin', 10));
    }

    public function testGetTreeReturnsNestedHierarchy(): void
    {
        $catRepo = $this->createMock(BudgetCategoryRepository::class);
        $catRepo->method('getTree')->willReturn([
            ['id' => 1, 'parent_id' => null, 'name_th' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name_th' => 'Child'],
        ]);

        $itemRepo = $this->createMock(BudgetCategoryItemRepository::class);
        $service = new BudgetCategoryService($catRepo, $itemRepo);

        $tree = $service->getTree();
        $this->assertCount(1, $tree);
        $this->assertSame(1, $tree[0]['id']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertSame(2, $tree[0]['children'][0]['id']);
    }
}

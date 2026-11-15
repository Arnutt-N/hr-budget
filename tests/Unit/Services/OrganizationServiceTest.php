<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dtos\UpdateOrganizationDto;
use App\Repositories\OrganizationRepository;
use App\Services\OrganizationService;
use PHPUnit\Framework\TestCase;

final class OrganizationServiceTest extends TestCase
{
    public function testUpdateRejectsSelfParent(): void
    {
        $repo = $this->createMock(OrganizationRepository::class);
        $repo->method('findById')->willReturn(['id' => 5, 'parent_id' => null, 'level' => 0]);
        $repo->expects($this->never())->method('update');

        $service = new OrganizationService($repo);

        $this->assertFalse($service->update('admin', 5, new UpdateOrganizationDto(parentId: 5)));
    }

    public function testUpdateRejectsAncestorCycle(): void
    {
        $repo = $this->createMock(OrganizationRepository::class);
        $repo->method('findById')->willReturnMap([
            [5, ['id' => 5, 'parent_id' => null, 'level' => 0]],
            [15, ['id' => 15, 'parent_id' => 10, 'level' => 2]],
            [10, ['id' => 10, 'parent_id' => 5, 'level' => 1]],
        ]);
        $repo->expects($this->never())->method('update');

        $service = new OrganizationService($repo);

        $this->assertFalse($service->update('admin', 5, new UpdateOrganizationDto(parentId: 15)));
    }

    public function testUpdateSyncsDescendantLevelsWhenParentChanges(): void
    {
        $repo = $this->createMock(OrganizationRepository::class);
        $repo->method('findById')->willReturnMap([
            [10, ['id' => 10, 'parent_id' => null, 'level' => 1]],
            [5, ['id' => 5, 'parent_id' => null, 'level' => 0]],
        ]);
        $repo->method('findByParentId')->willReturnCallback(
            static fn (int $parentId) => match ($parentId) {
                10 => [
                    ['id' => 20, 'parent_id' => 10, 'level' => 2],
                    ['id' => 21, 'parent_id' => 10, 'level' => 2],
                ],
                default => [],
            }
        );
        // First update is the moving org itself (parent_id + level), then each
        // child gets that level + 1.
        $expected = [
            [10, ['parent_id' => 5, 'level' => 1]],
            [20, ['level' => 2]],
            [21, ['level' => 2]],
        ];
        $repo->expects($this->exactly(3))
            ->method('update')
            ->willReturnCallback(function (int $id, array $data) use (&$expected): bool {
                $expectedRow = array_shift($expected);
                $this->assertSame($expectedRow, [$id, $data], "update({$id}) must match the expected transition");
                return true;
            });

        $service = new OrganizationService($repo);

        $this->assertTrue($service->update('admin', 10, new UpdateOrganizationDto(parentId: 5)));
    }
}

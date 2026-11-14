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
}

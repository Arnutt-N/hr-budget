<?php
declare(strict_types=1);

namespace Tests\Unit\Dtos;

use App\Dtos\AssignGrantDto;
use PHPUnit\Framework\TestCase;

class AssignGrantDtoTest extends TestCase
{
    /** @test */
    public function valid_organization_grant_passes(): void
    {
        $dto = new AssignGrantDto(roleId: 3, scopeType: 'organization', scopeRefId: 10);
        $this->assertSame([], $dto->validate());
    }

    /** @test */
    public function all_scope_must_not_carry_a_ref(): void
    {
        $dto = new AssignGrantDto(roleId: 3, scopeType: 'all', scopeRefId: 10);
        $this->assertArrayHasKey('scope_ref_id', $dto->validate());
    }

    /** @test */
    public function all_scope_without_ref_passes(): void
    {
        $dto = new AssignGrantDto(roleId: 3, scopeType: 'all', scopeRefId: null);
        $this->assertSame([], $dto->validate());
    }

    /** @test */
    public function non_all_scope_requires_a_ref(): void
    {
        $dto = new AssignGrantDto(roleId: 3, scopeType: 'organization', scopeRefId: null);
        $this->assertArrayHasKey('scope_ref_id', $dto->validate());
    }

    /** @test */
    public function invalid_scope_type_is_rejected(): void
    {
        $dto = new AssignGrantDto(roleId: 3, scopeType: 'galaxy', scopeRefId: 1);
        $this->assertArrayHasKey('scope_type', $dto->validate());
    }

    /** @test */
    public function missing_role_is_rejected(): void
    {
        $dto = new AssignGrantDto(roleId: 0, scopeType: 'all', scopeRefId: null);
        $this->assertArrayHasKey('role_id', $dto->validate());
    }
}

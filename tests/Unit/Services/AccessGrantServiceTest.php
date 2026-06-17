<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dtos\AssignGrantDto;
use App\Services\AccessGrantService;

/**
 * Privilege-escalation guards: a non-super actor must not grant access beyond
 * their own reach (privileged/system roles, 'all' scope, or out-of-subtree orgs).
 * Runs on in-memory SQLite seeded by {@see RbacSqliteTestCase}.
 */
class AccessGrantServiceTest extends RbacSqliteTestCase
{
    private AccessGrantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AccessGrantService();
    }

    /** Non-super actor who is org_admin over $orgId (carries user.manage). */
    private function orgAdminActor(int $orgId): array
    {
        $actor = $this->makeUser('editor');
        $this->grant($actor['id'], 'org_admin', 'organization', $orgId);
        return $actor;
    }

    /** @test */
    public function super_admin_can_assign_all_scope(): void
    {
        $admin = $this->makeAdmin();
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('executive'), 'all', null);

        $result = $this->service->assign($admin, $target['id'], $dto);

        $this->assertTrue($result['ok']);
    }

    /** @test */
    public function non_super_cannot_assign_all_scope(): void
    {
        $org = $this->makeOrg(null, 0);
        $actor = $this->orgAdminActor($org);
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('viewer'), 'all', null);

        $result = $this->service->assign($actor, $target['id'], $dto);

        $this->assertFalse($result['ok']);
        $this->assertSame('forbidden_all_scope', $result['error']);
    }

    /** @test */
    public function non_super_cannot_assign_outside_subtree(): void
    {
        $org = $this->makeOrg(null, 0);
        $otherOrg = $this->makeOrg(null, 0);
        $actor = $this->orgAdminActor($org);
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('viewer'), 'organization', $otherOrg);

        $result = $this->service->assign($actor, $target['id'], $dto);

        $this->assertFalse($result['ok']);
        $this->assertSame('forbidden_out_of_scope', $result['error']);
    }

    /** @test */
    public function non_super_can_assign_within_subtree(): void
    {
        $org = $this->makeOrg(null, 0);
        $child = $this->makeOrg($org, 1);
        $actor = $this->orgAdminActor($org);
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('viewer'), 'organization', $child);

        $result = $this->service->assign($actor, $target['id'], $dto);

        $this->assertTrue($result['ok']);
    }

    /** @test */
    public function non_super_cannot_assign_system_role(): void
    {
        $org = $this->makeOrg(null, 0);
        $actor = $this->orgAdminActor($org);
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('super_admin'), 'organization', $org);

        $result = $this->service->assign($actor, $target['id'], $dto);

        $this->assertFalse($result['ok']);
        $this->assertSame('forbidden_privileged_role', $result['error']);
    }

    /** @test */
    public function duplicate_grant_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        $org = $this->makeOrg(null, 0);
        $target = $this->makeUser();
        $dto = new AssignGrantDto($this->roleId('viewer'), 'organization', $org);

        $first = $this->service->assign($admin, $target['id'], $dto);
        $second = $this->service->assign($admin, $target['id'], $dto);

        $this->assertTrue($first['ok']);
        $this->assertFalse($second['ok']);
        $this->assertSame('duplicate_grant', $second['error']);
    }
}

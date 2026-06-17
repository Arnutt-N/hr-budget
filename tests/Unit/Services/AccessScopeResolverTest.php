<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AccessScopeResolver;

/**
 * Security core: org-subtree expansion, permission union, super-admin bypass.
 * Runs on in-memory SQLite seeded by {@see RbacSqliteTestCase}.
 */
class AccessScopeResolverTest extends RbacSqliteTestCase
{
    private AccessScopeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new AccessScopeResolver();
    }

    /** @test */
    public function super_admin_bypasses_all_scope(): void
    {
        $scope = $this->resolver->resolve($this->makeAdmin());

        $this->assertTrue($scope['isSuperAdmin']);
        $this->assertTrue($scope['hasAll']);
    }

    /** @test */
    public function organization_scope_expands_to_descendants(): void
    {
        $a = $this->makeOrg(null, 0);     // root
        $b = $this->makeOrg($a, 1);       // child of A
        $c = $this->makeOrg($b, 2);       // grandchild
        $other = $this->makeOrg(null, 0); // unrelated root

        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'viewer', 'organization', $a);

        $scope = $this->resolver->resolve($user);

        $this->assertFalse($scope['isSuperAdmin']);
        $this->assertContains($a, $scope['orgIds']);
        $this->assertContains($b, $scope['orgIds']);
        $this->assertContains($c, $scope['orgIds']);
        $this->assertNotContains($other, $scope['orgIds']);
        $this->assertContains('budget.view', $scope['permissions']);
        $this->assertNotContains('budget.edit', $scope['permissions']);
    }

    /** @test */
    public function all_scope_sets_has_all_without_org_list(): void
    {
        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'executive', 'all', null);

        $scope = $this->resolver->resolve($user);

        $this->assertTrue($scope['hasAll']);
        $this->assertSame([], $scope['orgIds']);
    }

    /** @test */
    public function user_without_grants_sees_nothing(): void
    {
        $scope = $this->resolver->resolve($this->makeUser('viewer'));

        $this->assertFalse($scope['isSuperAdmin']);
        $this->assertFalse($scope['hasAll']);
        $this->assertSame([], $scope['orgIds']);
        $this->assertSame([], $scope['permissions']);
    }

    /** @test */
    public function org_scope_filter_denies_when_no_access(): void
    {
        $filter = $this->resolver->orgScopeFilter($this->makeUser('viewer'));
        $this->assertSame('1=0', $filter['sql']);
    }

    /** @test */
    public function org_scope_filter_allows_all_for_super_admin(): void
    {
        $filter = $this->resolver->orgScopeFilter($this->makeAdmin());
        $this->assertSame('1=1', $filter['sql']);
    }

    /** @test */
    public function org_scope_filter_builds_in_clause_for_scoped_user(): void
    {
        $a = $this->makeOrg(null, 0);
        $b = $this->makeOrg($a, 1);
        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'viewer', 'organization', $a);

        $filter = $this->resolver->orgScopeFilter($user, 'organization_id');

        $this->assertStringContainsString('organization_id IN (', $filter['sql']);
        $this->assertContains($a, $filter['params']);
        $this->assertContains($b, $filter['params']);
    }
}

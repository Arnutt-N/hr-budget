<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dtos\DisbursementSessionListQueryDto;
use App\Services\DisbursementService;

/**
 * Phase 10: disbursement reads honour RBAC subtree grants, additively, mirroring
 * the budget-request scoping from Phase 9. A non-admin sees sessions in their own
 * org PLUS any org within a granted subtree; grants only widen the view. WRITE
 * paths stay own-org and are not exercised here.
 */
class DisbursementScopeTest extends DisbursementScopeTestCase
{
    private DisbursementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DisbursementService();
    }

    /** @return int[] ids returned by listSessions() for the given user */
    private function visibleIds(string $role, array $user): array
    {
        $res = $this->service->listSessions($role, $user, new DisbursementSessionListQueryDto(perPage: 100));
        return array_map(static fn ($r) => (int) $r['id'], $res['data']);
    }

    /** @test */
    public function admin_sees_all_sessions(): void
    {
        $org = $this->makeOrg(null, 0);
        $s1 = $this->makeSession($org, 999, 1);
        $s2 = $this->makeSession($org, 999, 2);

        $ids = $this->visibleIds('admin', $this->makeAdmin());

        $this->assertContains($s1, $ids);
        $this->assertContains($s2, $ids);
    }

    /** @test */
    public function ungranted_user_sees_only_own_org_sessions(): void
    {
        $ownOrg = $this->makeOrg(null, 0);
        $otherOrg = $this->makeOrg(null, 0);
        $user = $this->makeUser('viewer');

        $mine = $this->makeSession($ownOrg, $user['id'], 1);
        $theirs = $this->makeSession($otherOrg, $user['id'], 2);

        $ids = $this->visibleIds('viewer', $user + ['org_id' => $ownOrg]);

        $this->assertSame([$mine], $ids);
        $this->assertNotContains($theirs, $ids);
    }

    /** @test */
    public function org_granted_user_sees_own_plus_subtree(): void
    {
        $parent = $this->makeOrg(null, 0);
        $child = $this->makeOrg($parent, 1);   // descendant of parent
        $ownOrg = $this->makeOrg(null, 0);      // home org, outside grant subtree
        $outside = $this->makeOrg(null, 0);     // unrelated

        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'org_admin', 'organization', $parent);

        $inChild = $this->makeSession($child, 999, 1);
        $inParent = $this->makeSession($parent, 999, 2);
        $own = $this->makeSession($ownOrg, 999, 3);
        $hidden = $this->makeSession($outside, 999, 4);

        $ids = $this->visibleIds('viewer', $user + ['org_id' => $ownOrg]);

        $this->assertContains($inChild, $ids);
        $this->assertContains($inParent, $ids);
        $this->assertContains($own, $ids);
        $this->assertNotContains($hidden, $ids);
    }

    /** @test */
    public function all_scope_grant_sees_every_session(): void
    {
        $org = $this->makeOrg(null, 0);
        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'executive', 'all', null);

        $s1 = $this->makeSession($org, 999, 1);
        $s2 = $this->makeSession($org, 999, 2);

        $ids = $this->visibleIds('viewer', $user);

        $this->assertContains($s1, $ids);
        $this->assertContains($s2, $ids);
    }

    /** @test */
    public function getSession_opens_subtree_but_hides_out_of_scope(): void
    {
        $parent = $this->makeOrg(null, 0);
        $child = $this->makeOrg($parent, 1);
        $outside = $this->makeOrg(null, 0);

        $user = $this->makeUser('viewer');
        $this->grant($user['id'], 'org_admin', 'organization', $parent);
        $u = $user + ['org_id' => 0];

        $inSubtree = $this->makeSession($child, 999, 1);
        $outOfScope = $this->makeSession($outside, 999, 2);

        $this->assertNotNull($this->service->getSession('viewer', $u, $inSubtree));
        $this->assertNull($this->service->getSession('viewer', $u, $outOfScope));
    }
}

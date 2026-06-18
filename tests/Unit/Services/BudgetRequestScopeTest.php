<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dtos\BudgetRequestListQueryDto;
use App\Services\BudgetRequestService;

/**
 * RBAC additive visibility for the budget-request list (Phase 9):
 * super admin sees all; a viewer sees their own requests PLUS any request whose
 * organization is within their granted org subtree; grants only widen the view.
 * Runs on the in-memory SQLite RBAC fixture.
 */
class BudgetRequestScopeTest extends BudgetRequestScopeTestCase
{
    private BudgetRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetRequestService();
    }

    /** @return int[] ids returned by list() for the given user */
    private function visibleIds(array $user): array
    {
        $res = $this->service->list($user, new BudgetRequestListQueryDto(perPage: 100));
        return array_map(static fn ($r) => (int) $r['id'], $res['data']);
    }

    /** @test */
    public function admin_sees_all_requests(): void
    {
        $p = $this->makeOrg(null, 0);
        $other = $this->makeUser('viewer');
        $r1 = $this->makeRequest($p, $other['id']);
        $r2 = $this->makeRequest($p, $other['id']);

        $ids = $this->visibleIds($this->makeAdmin());

        $this->assertContains($r1, $ids);
        $this->assertContains($r2, $ids);
    }

    /** @test */
    public function ungranted_user_sees_only_own_requests(): void
    {
        $p = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $mine = $this->makeRequest($p, $owner['id']);
        $theirs = $this->makeRequest($p, $other['id']);

        $ids = $this->visibleIds($owner);

        $this->assertSame([$mine], $ids);
        $this->assertNotContains($theirs, $ids);
    }

    /** @test */
    public function org_granted_user_sees_own_plus_subtree(): void
    {
        $parent = $this->makeOrg(null, 0);
        $child = $this->makeOrg($parent, 1);   // descendant of parent
        $outside = $this->makeOrg(null, 0);     // unrelated org

        $approver = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $this->grant($approver['id'], 'org_admin', 'organization', $parent);

        $inChild = $this->makeRequest($child, $other['id']);   // in subtree, not own
        $inParent = $this->makeRequest($parent, $other['id']); // in subtree, not own
        $own = $this->makeRequest($outside, $approver['id']);  // own, out of subtree
        $hidden = $this->makeRequest($outside, $other['id']);  // neither

        $ids = $this->visibleIds($approver);

        $this->assertContains($inChild, $ids);
        $this->assertContains($inParent, $ids);
        $this->assertContains($own, $ids);
        $this->assertNotContains($hidden, $ids);
    }

    /** @test */
    public function findById_is_consistent_with_list_for_subtree_requests(): void
    {
        // A request visible in list() must also open in detail (no "404 on open").
        $parent = $this->makeOrg(null, 0);
        $child = $this->makeOrg($parent, 1);
        $approver = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $this->grant($approver['id'], 'org_admin', 'organization', $parent);

        $inSubtree = $this->makeRequest($child, $other['id']);
        $detail = $this->service->findById($approver['id'], $approver['role'], $inSubtree);

        $this->assertNotNull($detail);
        $this->assertSame($inSubtree, (int) $detail['id']);
    }

    /** @test */
    public function findById_hides_out_of_scope_request_from_ungranted_user(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $theirs = $this->makeRequest($org, $other['id']);

        // No grant + not the owner → not visible.
        $this->assertNull($this->service->findById($owner['id'], $owner['role'], $theirs));
    }

    /** @test */
    public function all_scope_grant_sees_every_request(): void
    {
        $p = $this->makeOrg(null, 0);
        $u = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $this->grant($u['id'], 'executive', 'all', null);

        $r1 = $this->makeRequest($p, $other['id']);
        $r2 = $this->makeRequest($p, $other['id']);

        $ids = $this->visibleIds($u);

        $this->assertContains($r1, $ids);
        $this->assertContains($r2, $ids);
    }
}

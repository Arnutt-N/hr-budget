<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Repositories\ApprovalChainRepository;
use App\Services\ApprovalChainService;

/**
 * Multi-step approval chain: advancement, finalization, rejection, and the
 * wrong-level / out-of-scope guards. Runs on the SQLite fixture.
 */
class ApprovalChainServiceTest extends ApprovalSqliteTestCase
{
    private ApprovalChainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApprovalChainService();
    }

    /** Approver of a given level over an org. */
    private function approver(string $roleCode, int $orgId): array
    {
        $u = $this->makeUser('editor');
        $this->grant($u['id'], $roleCode, 'organization', $orgId);
        return $u;
    }

    /** @test */
    public function division_approval_advances_to_department(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);
        $actor = $this->approver('approver_division', $org);

        $res = $this->service->act($actor, $req, 'approve', null);

        $this->assertTrue($res['ok']);
        $this->assertSame('pending', $res['status']);
        $this->assertSame(2, $res['level']);
    }

    /** @test */
    public function full_chain_finalizes_as_approved(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);

        $r1 = $this->service->act($this->approver('approver_division', $org), $req, 'approve', null);
        $r2 = $this->service->act($this->approver('approver_department', $org), $req, 'approve', null);
        $r3 = $this->service->act($this->approver('approver_ministry', $org), $req, 'approve', null);

        $this->assertSame(2, $r1['level']);
        $this->assertSame(3, $r2['level']);
        $this->assertTrue($r3['ok']);
        $this->assertSame('approved', $r3['status']);
        $this->assertNull($r3['level']);
    }

    /** @test */
    public function rejection_stops_the_chain(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);
        $actor = $this->approver('approver_division', $org);

        $res = $this->service->act($actor, $req, 'reject', 'งบไม่เพียงพอ');

        $this->assertTrue($res['ok']);
        $this->assertSame('rejected', $res['status']);
        $this->assertNull($res['level']);
    }

    /** @test */
    public function wrong_level_role_is_forbidden(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1); // needs approver_division
        $actor = $this->approver('approver_department', $org); // wrong level

        $res = $this->service->act($actor, $req, 'approve', null);

        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden_wrong_level_role', $res['error']);
    }

    /** @test */
    public function out_of_scope_org_is_forbidden(): void
    {
        $org = $this->makeOrg(null, 0);
        $otherOrg = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);
        $actor = $this->approver('approver_division', $otherOrg); // scoped elsewhere

        $res = $this->service->act($actor, $req, 'approve', null);

        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden_out_of_scope', $res['error']);
    }

    /** @test */
    public function super_admin_can_act_at_any_level(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 2); // at department level
        $admin = $this->makeAdmin();

        $res = $this->service->act($admin, $req, 'approve', null);

        $this->assertTrue($res['ok']);
        $this->assertSame(3, $res['level']); // advanced to ministry
    }

    /** @test */
    public function cannot_act_on_non_pending_request(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'approved', null);
        $actor = $this->approver('approver_division', $org);

        $res = $this->service->act($actor, $req, 'approve', null);

        $this->assertFalse($res['ok']);
        $this->assertContains($res['error'], ['not_in_chain', 'not_pending']);
    }

    /** @test */
    public function statusForUser_denies_out_of_scope_non_owner(): void
    {
        $org = $this->makeOrg(null, 0);
        $otherOrg = $this->makeOrg(null, 0);
        $this->makeUser('viewer'); // id=1 (matches makeRequest created_by=1)
        $req = $this->makeRequest($org, 'pending', 1);
        $outsider = $this->makeUser('viewer'); // id=2
        $this->grant($outsider['id'], 'viewer', 'organization', $otherOrg);

        $res = $this->service->statusForUser($outsider, $req);

        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden_out_of_scope', $res['error']);
    }

    /** @test */
    public function statusForUser_allows_org_scoped_viewer(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);
        $viewer = $this->makeUser('viewer');
        $this->grant($viewer['id'], 'viewer', 'organization', $org);

        $res = $this->service->statusForUser($viewer, $req);

        $this->assertTrue($res['ok']);
        $this->assertSame('pending', $res['data']['request_status']);
        $this->assertSame(1, $res['data']['current_level']);
    }

    /** @test */
    public function conditional_update_loses_when_row_already_advanced(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);

        $this->service->act($this->approver('approver_division', $org), $req, 'approve', null);

        $repo = new \App\Repositories\ApprovalChainRepository();
        $applied = $repo->updateRequestAtLevel($req, 1, ['current_level' => 2]);

        $this->assertFalse($applied, 'stale conditional update must not apply');
        $row = Database::queryOne('SELECT * FROM budget_requests WHERE id = ?', [$req]);
        $this->assertSame(2, (int) $row['current_level']);
        $this->assertCount(1, $this->service->status($req)['history']);
    }

    /** @test */
    public function conditional_update_applies_at_matching_level(): void
    {
        $org = $this->makeOrg(null, 0);
        $req = $this->makeRequest($org, 'pending', 1);

        $repo = new \App\Repositories\ApprovalChainRepository();
        $applied = $repo->updateRequestAtLevel($req, 1, ['current_level' => 2]);

        $this->assertTrue($applied);
        $row = Database::queryOne('SELECT * FROM budget_requests WHERE id = ?', [$req]);
        $this->assertSame(2, (int) $row['current_level']);
    }

    /** @test */
    public function cannot_self_approve_own_request(): void
    {
        $org = $this->makeOrg(null, 0);
        $actor = $this->approver('approver_division', $org);
        $req = $this->makeRequest($org, 'pending', 1, createdBy: $actor['id']);

        $res = $this->service->act($actor, $req, 'approve', null);

        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden_self_approve', $res['error']);
    }
}

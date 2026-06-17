<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

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
}

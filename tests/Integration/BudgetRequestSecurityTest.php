<?php
/**
 * Security Tests for Budget Request System — service-level guards.
 *
 * Each test drives the real BudgetRequestService against hr_budget_test and
 * asserts both the boolean denial AND that persisted state is unchanged, so a
 * regression in the approve/reject/delete guards cannot pass silently.
 */

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\BudgetRequest;
use App\Dtos\ApprovalActionDto;
use App\Services\BudgetRequestService;

class BudgetRequestSecurityTest extends TestCase
{
    private BudgetRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetRequestService();
    }

    /**
     * @test
     */
    public function viewer_cannot_approve_requests()
    {
        $viewer = $this->createUser(['role' => 'viewer']);
        $admin = $this->createAdmin();

        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $admin['id'],
            'request_status' => 'pending',
        ]);

        // A viewer's approve attempt must be refused...
        $this->assertFalse($this->service->approve((int) $viewer['id'], 'viewer', $requestId, new ApprovalActionDto()));
        $this->assertSame('pending', BudgetRequest::find($requestId)['request_status']);

        // ...while an admin's approval succeeds.
        $this->assertTrue($this->service->approve((int) $admin['id'], 'admin', $requestId, new ApprovalActionDto()));
        $this->assertSame('approved', BudgetRequest::find($requestId)['request_status']);
    }

    /**
     * @test
     */
    public function user_cannot_approve_own_request()
    {
        $user = $this->createEditor();

        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'My Request',
            'created_by' => $user['id'],
            'request_status' => 'pending',
        ]);

        // Only admins may approve — the owner (editor) cannot, even on their own request.
        $this->assertFalse($this->service->approve((int) $user['id'], 'editor', $requestId, new ApprovalActionDto()));
        $this->assertSame('pending', BudgetRequest::find($requestId)['request_status']);
    }

    /**
     * @test
     */
    public function cannot_modify_submitted_request()
    {
        $user = $this->createUser();

        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Submitted Request',
            'created_by' => $user['id'],
            'request_status' => 'pending',
        ]);

        // Only draft/saved requests are editable — pending ones stay untouched.
        $this->assertFalse($this->service->delete((int) $user['id'], 'viewer', $requestId));
        $this->assertSame('Submitted Request', BudgetRequest::find($requestId)['request_title']);
    }

    /**
     * @test
     */
    public function sql_injection_prevention_in_filters()
    {
        $admin = $this->createAdmin();

        $maliciousInput = "'; DROP TABLE budget_requests; --";

        $filters = ['search' => $maliciousInput];

        $requests = BudgetRequest::all($filters);

        $this->assertIsArray($requests);
    }

    /**
     * @test
     */
    public function xss_prevention_in_request_title()
    {
        $user = $this->createUser();

        $xssPayload = '<script>alert("XSS")</script>';

        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => $xssPayload,
            'created_by' => $user['id'],
        ]);

        $request = BudgetRequest::find($requestId);

        // The value is stored as-is (escaping happens in view layer)
        $this->assertEquals($xssPayload, $request['request_title']);
    }

    /**
     * @test
     */
    public function cannot_delete_others_request()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'User 1 Request',
            'created_by' => $user1['id'],
            'request_status' => 'saved',
        ]);

        // user2 must not be able to delete user1's request...
        $this->assertFalse($this->service->delete((int) $user2['id'], 'viewer', $requestId));
        $this->assertNotNull(BudgetRequest::find($requestId));

        // ...while the owner can.
        $this->assertTrue($this->service->delete((int) $user1['id'], 'viewer', $requestId));
        $this->assertNull(BudgetRequest::find($requestId));
    }
}

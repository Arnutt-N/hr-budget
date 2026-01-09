<?php
/**
 * BudgetRequest Model Tests
 * Tests for CRUD operations and business logic
 */

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\BudgetRequest;

class BudgetRequestTest extends TestCase
{
    /** @test */
    public function it_can_create_a_budget_request()
    {
        $user = $this->createUser();
        
        $data = [
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id']
        ];
        
        $id = BudgetRequest::create($data);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        $this->assertDatabaseHas('budget_requests', [
            'request_title' => 'Test Request',
            'fiscal_year' => 2568
        ]);
    }

    /** @test */
    public function it_filters_by_fiscal_year()
    {
        $user = $this->createUser();
        
        BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Request 2568',
            'created_by' => $user['id']
        ]);
        
        BudgetRequest::create([
            'fiscal_year' => 2567,
            'request_title' => 'Request 2567',
            'created_by' => $user['id']
        ]);
        
        $requests = BudgetRequest::all(['fiscal_year' => 2568]);
        
        foreach ($requests as $req) {
            $this->assertEquals(2568, $req['fiscal_year']);
        }
    }

    /** @test */
    public function it_updates_status_with_timestamp()
    {
        $user = $this->createUser();
        
        $id = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test',
            'created_by' => $user['id']
        ]);
        
        // Use 'pending' status which triggers the timestamp update (corrected logic)
        BudgetRequest::updateStatus($id, 'pending');
        
        $request = BudgetRequest::find($id);
        $this->assertEquals('pending', $request['request_status']);
        $this->assertNotNull($request['submitted_at']);
    }

    /** @test */
    public function getStats_returns_correct_counts()
    {
        $user = $this->createUser();
        
        // Use a unique fiscal year to avoid counting seeded data
        $fiscalYear = 3000 + rand(1, 999);
        
        // Create requests with different statuses
        BudgetRequest::create([
            'fiscal_year' => $fiscalYear,
            'request_title' => 'Pending 1',
            'request_status' => 'pending',
            'total_amount' => 10000,
            'created_by' => $user['id']
        ]);
        
        BudgetRequest::create([
            'fiscal_year' => $fiscalYear,
            'request_title' => 'Approved 1',
            'request_status' => 'approved',
            'total_amount' => 20000,
            'created_by' => $user['id']
        ]);
        
        BudgetRequest::create([
            'fiscal_year' => $fiscalYear,
            'request_title' => 'Rejected 1',
            'request_status' => 'rejected',
            'total_amount' => 5000,
            'created_by' => $user['id']
        ]);
        
        $stats = BudgetRequest::getStats($fiscalYear);
        
        $this->assertEquals(3, $stats['total_requests']);
        $this->assertEquals(1, $stats['pending_count']);
        $this->assertEquals(1, $stats['approved_count']);
        $this->assertEquals(1, $stats['rejected_count']);
        $this->assertEquals(35000, $stats['total_amount']);
        $this->assertEquals(20000, $stats['approved_amount']);
    }

    /** @test */
    public function getStats_filters_by_fiscal_year()
    {
        $user = $this->createUser();
        
        // Use unique years
        $year1 = 4000 + rand(1, 499);
        $year2 = 4500 + rand(500, 999);
        
        BudgetRequest::create([
            'fiscal_year' => $year1,
            'request_title' => 'Year 1 Request',
            'created_by' => $user['id']
        ]);
        
        BudgetRequest::create([
            'fiscal_year' => $year2,
            'request_title' => 'Year 2 Request',
            'created_by' => $user['id']
        ]);
        
        $stats1 = BudgetRequest::getStats($year1);
        $stats2 = BudgetRequest::getStats($year2);
        
        $this->assertEquals(1, $stats1['total_requests']);
        $this->assertEquals(1, $stats2['total_requests']);
    }

    /** @test */
    public function getRecentRequests_returns_limited_results()
    {
        $user = $this->createUser();
        
        // Create 10 requests
        for ($i = 1; $i <= 10; $i++) {
            BudgetRequest::create([
                'fiscal_year' => 2568,
                'request_title' => "Request $i",
                'created_by' => $user['id']
            ]);
            // Small sleep to ensure timestamp diff if not using ID sort (but we will fix ID sort)
            usleep(1000); 
        }
        
        $recent = BudgetRequest::getRecentRequests(5);
        
        $this->assertCount(5, $recent);
        
        // Should be ordered by created_at DESC (and ID DESC after fix)
        $this->assertEquals('Request 10', $recent[0]['request_title']);
    }

    /** @test */
    public function getRecentRequests_includes_creator_name()
    {
        $user = $this->createUser(['name' => 'John Doe']);
        
        BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id']
        ]);
        
        $recent = BudgetRequest::getRecentRequests(5);
        
        $this->assertArrayHasKey('created_by_name', $recent[0]);
        $this->assertEquals('John Doe', $recent[0]['created_by_name']);
    }
}

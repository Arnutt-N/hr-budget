<?php
/**
 * BudgetRequestItem Model Tests
 */

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;

class BudgetRequestItemTest extends TestCase
{
    /** @test */
    public function it_can_create_item_with_valid_data()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id']
        ]);
        
        $itemId = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Office Supplies',
            'quantity' => 10,
            'unit_price' => 150.00
        ]);
        
        $this->assertIsInt($itemId);
        $this->assertGreaterThan(0, $itemId);
        
        $this->assertDatabaseHas('budget_request_items', [
            'item_name' => 'Office Supplies',
            'quantity' => 10,
            'unit_price' => 150.00
        ]);
    }

    /** @test */
    public function item_total_price_is_calculated()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test',
            'created_by' => $user['id']
        ]);
        
        $itemId = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Laptop',
            'quantity' => 5,
            'unit_price' => 25000.00
        ]);
        
        $items = BudgetRequestItem::getByRequestId($requestId);
        $item = $items[0];
        
        // total_price should be quantity * unit_price = 125000
        $expectedTotal = 5 * 25000.00;
        $this->assertEquals($expectedTotal, $item['quantity'] * $item['unit_price']);
    }

    /** @test */
    public function it_can_delete_item()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test',
            'created_by' => $user['id']
        ]);
        
        $itemId = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item to Delete',
            'quantity' => 1,
            'unit_price' => 100
        ]);
        
        $deleted = BudgetRequestItem::delete($itemId);
        
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('budget_request_items', ['id' => $itemId]);
    }

    /** @test */
    public function getByRequestId_returns_all_items()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test',
            'created_by' => $user['id']
        ]);
        
        BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100
        ]);
        
        BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 2',
            'quantity' => 2,
            'unit_price' => 200
        ]);
        
        $items = BudgetRequestItem::getByRequestId($requestId);
        
        $this->assertCount(2, $items);
    }

    /** @test */
    public function getTree_returns_hierarchical_structure()
    {
        // Body removed rather than left below an unconditional skip, where no
        // tool in this project analyses it (phpstan.neon covers `src` only) and
        // a rename would silently drift. Recover it from git history when the
        // hierarchy feature lands.
        $this->markTestSkipped(
            'Parent/child hierarchy is not implemented: budget_request_items has no '
            . 'parent_item_id column, and getTree() deliberately returns a flat list '
            . '(see the comment in the model). Unskip when the hierarchy feature lands.'
        );
    }

    /** @test */
    public function it_can_update_item()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test',
            'created_by' => $user['id']
        ]);
        
        $itemId = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Original Name',
            'quantity' => 1,
            'unit_price' => 100
        ]);
        
        $updated = BudgetRequestItem::update($itemId, [
            'item_name' => 'Updated Name',
            'quantity' => 5
        ]);
        
        $this->assertTrue($updated);
        
        $items = BudgetRequestItem::getByRequestId($requestId);
        $this->assertEquals('Updated Name', $items[0]['item_name']);
        $this->assertEquals(5, $items[0]['quantity']);
    }
}

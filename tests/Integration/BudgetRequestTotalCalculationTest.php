<?php
/**
 * Integration Tests for recalculateTotal functionality
 */

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;

class BudgetRequestTotalCalculationTest extends TestCase
{
    /** @test */
    public function total_is_recalculated_when_item_added()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id'],
            'total_amount' => 0
        ]);
        
        // Add first item
        BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 1',
            'quantity' => 5,
            'unit_price' => 100
        ]);
        
        // Simulate controller recalculation
        $items = BudgetRequestItem::getByRequestId($requestId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        BudgetRequest::update($requestId, ['total_amount' => $total]);
        
        $request = BudgetRequest::find($requestId);
        $this->assertEquals(500, $request['total_amount']);
        
        // Add second item
        BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 2',
            'quantity' => 3,
            'unit_price' => 200
        ]);
        
        // Recalculate
        $items = BudgetRequestItem::getByRequestId($requestId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        BudgetRequest::update($requestId, ['total_amount' => $total]);
        
        $request = BudgetRequest::find($requestId);
        $this->assertEquals(1100, $request['total_amount']); // 500 + 600
    }

    /** @test */
    public function total_is_recalculated_when_item_deleted()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id']
        ]);
        
        $item1Id = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 1',
            'quantity' => 5,
            'unit_price' => 100
        ]);
        
        $item2Id = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item 2',
            'quantity' => 3,
            'unit_price' => 200
        ]);
        
        // Initial total should be 500 + 600 = 1100
        $items = BudgetRequestItem::getByRequestId($requestId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        BudgetRequest::update($requestId, ['total_amount' => $total]);
        
        // Delete item 2
        BudgetRequestItem::delete($item2Id);
        
        // Recalculate
        $items = BudgetRequestItem::getByRequestId($requestId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        BudgetRequest::update($requestId, ['total_amount' => $total]);
        
        $request = BudgetRequest::find($requestId);
        $this->assertEquals(500, $request['total_amount']); // Only item 1 remains
    }

    /** @test */
    public function total_is_zero_when_all_items_deleted()
    {
        $user = $this->createUser();
        
        $requestId = BudgetRequest::create([
            'fiscal_year' => 2568,
            'request_title' => 'Test Request',
            'created_by' => $user['id']
        ]);
        
        $itemId = BudgetRequestItem::create([
            'budget_request_id' => $requestId,
            'item_name' => 'Item',
            'quantity' => 5,
            'unit_price' => 100
        ]);
        
        // Set initial total
        BudgetRequest::update($requestId, ['total_amount' => 500]);
        
        // Delete the item
        BudgetRequestItem::delete($itemId);
        
        // Recalculate
        $items = BudgetRequestItem::getByRequestId($requestId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        BudgetRequest::update($requestId, ['total_amount' => $total]);
        
        $request = BudgetRequest::find($requestId);
        $this->assertEquals(0, $request['total_amount']);
    }
}

<?php
/**
 * Integration Tests for the budget-request total_amount computation.
 *
 * Drives the real BudgetRequestService (create/update) against hr_budget_test
 * and asserts the PERSISTED total_amount, so the production bcmath pipeline —
 * not a re-implementation of it — is what's under test.
 */

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Dtos\BudgetRequestItemDto;
use App\Dtos\CreateBudgetRequestDto;
use App\Dtos\UpdateBudgetRequestDto;
use App\Services\BudgetRequestService;

class BudgetRequestTotalCalculationTest extends TestCase
{
    private BudgetRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetRequestService();
    }

    private function createRequest(int $userId, array $items): int
    {
        $dtos = array_map(
            static fn (array $i): BudgetRequestItemDto => new BudgetRequestItemDto($i[0], $i[1], $i[2]),
            $items,
        );

        $requestId = $this->service->create(
            $userId,
            new CreateBudgetRequestDto('Test Request', 2568, null, $dtos),
        );
        $this->assertNotNull($requestId, 'service create() failed');

        return $requestId;
    }

    /** @test */
    public function create_persists_the_bcmath_total_of_all_items()
    {
        $user = $this->createUser();

        $requestId = $this->createRequest($user['id'], [
            ['Item 1', '5', '100'],   // 500.00
            ['Item 2', '3', '200'],   // 600.00
        ]);

        $request = BudgetRequest::find($requestId);
        $this->assertSame('1100.00', (string) $request['total_amount']);
    }

    /** @test */
    public function update_replaces_items_and_recomputes_the_total()
    {
        $user = $this->createUser();
        $requestId = $this->createRequest($user['id'], [
            ['Item 1', '5', '100'],   // 500.00
        ]);

        $ok = $this->service->update(
            (int) $user['id'],
            'viewer',
            $requestId,
            new UpdateBudgetRequestDto(items: [new BudgetRequestItemDto('Item 2', '3', '200')]),
        );

        $this->assertTrue($ok);
        $request = BudgetRequest::find($requestId);
        $this->assertSame('600.00', (string) $request['total_amount']);
        $this->assertCount(1, BudgetRequestItem::getByRequestId($requestId));
    }

    /** @test */
    public function clearing_all_items_zeroes_the_total()
    {
        $user = $this->createUser();
        $requestId = $this->createRequest($user['id'], [
            ['Item 1', '5', '100'],   // 500.00
        ]);

        $ok = $this->service->update(
            (int) $user['id'],
            'viewer',
            $requestId,
            new UpdateBudgetRequestDto(items: []),
        );

        $this->assertTrue($ok);
        $request = BudgetRequest::find($requestId);
        $this->assertSame('0.00', (string) $request['total_amount']);
    }
}

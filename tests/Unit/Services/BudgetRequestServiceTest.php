<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\BudgetRequestService;
use App\Dtos\ApprovalActionDto;
use App\Dtos\BudgetRequestItemDto;
use App\Dtos\CreateBudgetRequestDto;
use App\Dtos\UpdateBudgetRequestDto;
use App\Repositories\BudgetRequestRepository;
use App\Repositories\BudgetRequestItemRepository;
use App\Repositories\BudgetRequestApprovalRepository;

/**
 * Lightweight stubs for final repository classes.
 * These record calls so we can assert behavior.
 */
class StubRequestRepo extends BudgetRequestRepository
{
    public ?array $lastFindById = null;
    public ?int $insertResult = null;
    public ?array $insertCalledWith = null;
    public ?array $lastUpdateData = null;
    public bool $deleteCalled = false;
    private ?array $findByIdReturn;

    public function __construct(?array $findByIdReturn = []) {
        $this->findByIdReturn = $findByIdReturn;
    }

    public function findById(int $id): ?array { $this->lastFindById = ['id' => $id]; return $this->findByIdReturn; }
    public function insert(array $data): int { $this->insertCalledWith = $data; return $this->insertResult ?? 1; }
    public function update(int $id, array $data): bool { $this->lastUpdateData = ['id' => $id, 'data' => $data]; return true; }
    public function delete(int $id): bool { $this->deleteCalled = true; return true; }
    public function findAll(array $filters, int $limit, int $offset): array { return []; }
    public function count(array $filters): int { return 0; }
}

class StubItemRepo extends BudgetRequestItemRepository
{
    public array $insertedItems = [];
    public bool $replaceCalled = false;

    public function findByRequestId(int $requestId): array { return []; }
    public function insert(array $data): int { $this->insertedItems[] = $data; return 1; }
    public function replaceItems(int $requestId, array $itemRows): void { $this->replaceCalled = true; }
    public function deleteByRequestId(int $requestId): bool { return true; }
    public function delete(int $id): bool { return true; }
}

class StubApprovalRepo extends BudgetRequestApprovalRepository
{
    public array $logs = [];

    public function log(int $requestId, string $action, int $userId, ?string $note = null): int {
        $this->logs[] = ['request_id' => $requestId, 'action' => $action, 'user_id' => $userId, 'note' => $note];
        return count($this->logs);
    }
    public function findByRequestId(int $requestId): array { return []; }
}

class BudgetRequestServiceTest extends TestCase
{
    private function makeService(?array $requestRow = []): array {
        $reqRepo = new StubRequestRepo($requestRow);
        $itemRepo = new StubItemRepo();
        $approvalRepo = new StubApprovalRepo();
        $service = new BudgetRequestService($reqRepo, $itemRepo, $approvalRepo);
        return [$service, $reqRepo, $itemRepo, $approvalRepo];
    }

    // --- CREATE ---

    /** @test */
    public function create_inserts_request_and_items_and_logs(): void
    {
        [$service, $reqRepo, $itemRepo, $approvalRepo] = $this->makeService();
        $dto = new CreateBudgetRequestDto('Test', 2569, null, [
            new BudgetRequestItemDto('Item A', '2', '100'),
        ]);

        $id = $service->create(5, $dto);
        $this->assertSame(1, $id);
        $this->assertCount(1, $itemRepo->insertedItems);
        $this->assertCount(1, $approvalRepo->logs);
        $this->assertSame('created', $approvalRepo->logs[0]['action']);
    }

    /** @test */
    public function create_calculates_total_amount(): void
    {
        [$service, $reqRepo] = $this->makeService();
        $dto = new CreateBudgetRequestDto('Test', 2569, null, [
            new BudgetRequestItemDto('A', '2', '100'),
            new BudgetRequestItemDto('B', '3', '50'),
        ]);

        $service->create(5, $dto);
        $this->assertSame('350.00', $reqRepo->insertCalledWith['total_amount']);
    }

    // --- FIND BY ID ---

    /** @test */
    public function findById_returns_null_for_missing(): void
    {
        [$service] = $this->makeService(null);
        $this->assertNull($service->findById(5, 'staff', 999));
    }

    /** @test */
    public function findById_returns_null_for_other_users_request(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 10]);
        $this->assertNull($service->findById(5, 'staff', 1));
    }

    /** @test */
    public function findById_admin_sees_all(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 10]);
        $result = $service->findById(1, 'admin', 1);
        $this->assertNotNull($result);
        $this->assertSame(1, $result['id']);
    }

    // --- SUBMIT ---

    /** @test */
    public function submit_changes_draft_to_pending(): void
    {
        [$service, $reqRepo, , $approvalRepo] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'draft']);

        $this->assertTrue($service->submit(5, 1));
        $this->assertSame('pending', $reqRepo->lastUpdateData['data']['request_status']);
        $this->assertArrayHasKey('submitted_at', $reqRepo->lastUpdateData['data']);
        $this->assertSame('submitted', $approvalRepo->logs[0]['action']);
    }

    /** @test */
    public function submit_fails_for_wrong_owner(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 10, 'request_status' => 'draft']);
        $this->assertFalse($service->submit(5, 1));
    }

    /** @test */
    public function submit_fails_for_already_pending(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'pending']);
        $this->assertFalse($service->submit(5, 1));
    }

    // --- APPROVE ---

    /** @test */
    public function approve_changes_pending_to_approved(): void
    {
        [$service, $reqRepo, , $approvalRepo] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'pending']);
        $dto = new ApprovalActionDto('LG');

        $this->assertTrue($service->approve(3, 1, $dto));
        $this->assertSame('approved', $reqRepo->lastUpdateData['data']['request_status']);
        $this->assertSame('approved', $approvalRepo->logs[0]['action']);
    }

    /** @test */
    public function approve_fails_for_draft(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'draft']);
        $this->assertFalse($service->approve(3, 1, new ApprovalActionDto()));
    }

    // --- REJECT ---

    /** @test */
    public function reject_changes_pending_to_rejected_with_reason(): void
    {
        [$service, $reqRepo] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'pending']);
        $dto = new ApprovalActionDto('Incomplete docs');

        $this->assertTrue($service->reject(3, 1, $dto));
        $this->assertSame('rejected', $reqRepo->lastUpdateData['data']['request_status']);
        $this->assertSame('Incomplete docs', $reqRepo->lastUpdateData['data']['rejected_reason']);
    }

    // --- UPDATE ---

    /** @test */
    public function update_fails_for_approved_request(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'approved']);
        $this->assertFalse($service->update(5, 'staff', 1, new UpdateBudgetRequestDto(requestTitle: 'New')));
    }

    /** @test */
    public function update_succeeds_for_draft(): void
    {
        [$service, $reqRepo, , $approvalRepo] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'draft']);

        $this->assertTrue($service->update(5, 'staff', 1, new UpdateBudgetRequestDto(requestTitle: 'Updated')));
        $this->assertSame('Updated', $reqRepo->lastUpdateData['data']['request_title']);
        $this->assertSame('modified', $approvalRepo->logs[0]['action']);
    }

    // --- DELETE ---

    /** @test */
    public function delete_fails_for_pending(): void
    {
        [$service] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'pending']);
        $this->assertFalse($service->delete(5, 'staff', 1));
    }

    /** @test */
    public function delete_succeeds_for_draft(): void
    {
        [$service, $reqRepo, , $approvalRepo] = $this->makeService(['id' => 1, 'created_by' => 5, 'request_status' => 'draft']);

        $this->assertTrue($service->delete(5, 'staff', 1));
        $this->assertTrue($reqRepo->deleteCalled);
        $this->assertSame('deleted', $approvalRepo->logs[0]['action']);
    }
}

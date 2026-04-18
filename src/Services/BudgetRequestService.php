<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ApprovalActionDto;
use App\Dtos\BudgetRequestListQueryDto;
use App\Dtos\CreateBudgetRequestDto;
use App\Dtos\UpdateBudgetRequestDto;
use App\Repositories\BudgetRequestApprovalRepository;
use App\Repositories\BudgetRequestItemRepository;
use App\Repositories\BudgetRequestRepository;

final class BudgetRequestService
{
    private const EDITABLE_STATUSES = ['draft', 'saved'];

    public function __construct(
        private readonly BudgetRequestRepository $requestRepo = new BudgetRequestRepository(),
        private readonly BudgetRequestItemRepository $itemRepo = new BudgetRequestItemRepository(),
        private readonly BudgetRequestApprovalRepository $approvalRepo = new BudgetRequestApprovalRepository(),
    ) {}

    /**
     * List requests with filters and pagination.
     *
     * @return array{data: array[], meta: array{total: int, page: int, per_page: int}}
     */
    public function list(int $userId, string $role, BudgetRequestListQueryDto $query): array
    {
        $filters = $query->toFilters();

        if ($role !== 'admin') {
            $filters['created_by'] = $userId;
        }

        $total = $this->requestRepo->count($filters);
        $data = $this->requestRepo->findAll($filters, $query->perPage, $query->offset());

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $query->page,
                'per_page' => $query->perPage,
                'total_pages' => $query->perPage > 0 ? (int) ceil($total / $query->perPage) : 0,
            ],
        ];
    }

    /**
     * Create a new budget request with items.
     */
    public function create(int $userId, CreateBudgetRequestDto $dto): ?int
    {
        $totalAmount = '0';
        $itemRows = [];
        foreach ($dto->items as $item) {
            $row = $item->toInsertArray();
            $itemRows[] = $row;
            $totalAmount = bcadd($totalAmount, $item->amount(), 2);
        }

        $requestId = $this->requestRepo->insert([
            'fiscal_year' => $dto->fiscalYear,
            'request_title' => $dto->requestTitle,
            'request_status' => 'draft',
            'total_amount' => $totalAmount,
            'created_by' => $userId,
            'org_id' => $dto->orgId,
        ]);

        foreach ($itemRows as $row) {
            $row['budget_request_id'] = $requestId;
            $this->itemRepo->insert($row);
        }

        $this->approvalRepo->log($requestId, 'created', $userId);

        return $requestId;
    }

    /**
     * Find a request by ID with items and approval history.
     */
    public function findById(int $userId, string $role, int $id): ?array
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return null;
        }

        if ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
            return null;
        }

        $request['items'] = $this->itemRepo->findByRequestId($id);
        $request['approvals'] = $this->approvalRepo->findByRequestId($id);

        return $request;
    }

    /**
     * Update a draft/saved request.
     */
    public function update(int $userId, string $role, int $id, UpdateBudgetRequestDto $dto): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
            return false;
        }

        if (!in_array($request['request_status'], self::EDITABLE_STATUSES, true)) {
            return false;
        }

        $updateData = [];
        if ($dto->requestTitle !== null) {
            $updateData['request_title'] = $dto->requestTitle;
        }
        if ($dto->fiscalYear !== null) {
            $updateData['fiscal_year'] = $dto->fiscalYear;
        }
        if ($dto->orgId !== null) {
            $updateData['org_id'] = $dto->orgId;
        }

        if ($dto->items !== null) {
            $totalAmount = '0';
            $itemRows = [];
            foreach ($dto->items as $item) {
                $itemRows[] = $item->toInsertArray();
                $totalAmount = bcadd($totalAmount, $item->amount(), 2);
            }
            $updateData['total_amount'] = $totalAmount;
        }

        if (!empty($updateData) || $dto->items !== null) {
            $this->requestRepo->update($id, $updateData);

            if ($dto->items !== null) {
                $this->itemRepo->replaceItems($id, $itemRows);
            }

            $this->approvalRepo->log($id, 'modified', $userId);
        }

        return true;
    }

    /**
     * Delete a draft/saved request.
     */
    public function delete(int $userId, string $role, int $id): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
            return false;
        }

        if (!in_array($request['request_status'], self::EDITABLE_STATUSES, true)) {
            return false;
        }

        $this->approvalRepo->log($id, 'deleted', $userId);
        $this->requestRepo->delete($id);

        return true;
    }

    /**
     * Submit a draft/saved request for approval.
     */
    public function submit(int $userId, int $id): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ((int) $request['created_by'] !== $userId) {
            return false;
        }

        if (!in_array($request['request_status'], self::EDITABLE_STATUSES, true)) {
            return false;
        }

        $this->requestRepo->update($id, [
            'request_status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->approvalRepo->log($id, 'submitted', $userId);

        return true;
    }

    /**
     * Approve a pending request.
     */
    public function approve(int $userId, int $id, ApprovalActionDto $dto): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($request['request_status'] !== 'pending') {
            return false;
        }

        $this->requestRepo->update($id, [
            'request_status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->approvalRepo->log($id, 'approved', $userId, $dto->note);

        return true;
    }

    /**
     * Reject a pending request.
     */
    public function reject(int $userId, int $id, ApprovalActionDto $dto): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($request['request_status'] !== 'pending') {
            return false;
        }

        $this->requestRepo->update($id, [
            'request_status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_reason' => $dto->note,
        ]);

        $this->approvalRepo->log($id, 'rejected', $userId, $dto->note);

        return true;
    }
}

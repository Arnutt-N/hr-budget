<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
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
        private readonly NotificationService $notificationService = new NotificationService(),
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
     * Create a new budget request with items (atomic).
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

        Database::beginTransaction();
        try {
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
            Database::commit();
            return $requestId;
        } catch (\Throwable $e) {
            Database::rollback();
            return null;
        }
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
            Database::beginTransaction();
            try {
                $this->requestRepo->update($id, $updateData);

                if ($dto->items !== null) {
                    $this->itemRepo->replaceItemsUnsafe($id, $itemRows);
                }

                $this->approvalRepo->log($id, 'modified', $userId);
                Database::commit();
            } catch (\Throwable $e) {
                Database::rollback();
                return false;
            }
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

        Database::beginTransaction();
        try {
            $this->requestRepo->delete($id);
            $this->approvalRepo->log($id, 'deleted', $userId);
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }
    }

    /**
     * Submit a draft/saved request for approval (atomic).
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

        Database::beginTransaction();
        try {
            $updated = $this->requestRepo->updateWhereStatus($id, $request['request_status'], [
                'request_status' => 'pending',
                'submitted_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$updated) {
                Database::rollback();
                return false;
            }

            $this->approvalRepo->log($id, 'submitted', $userId);
            Database::commit();

            $this->dispatchSubmitNotifications($id, $request, $userId);
            return true;
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }
    }

    /**
     * Approve a pending request (atomic, admin-only).
     */
    public function approve(int $userId, string $role, int $id, ApprovalActionDto $dto): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($request['request_status'] !== 'pending') {
            return false;
        }

        if ($role !== 'admin') {
            return false;
        }

        Database::beginTransaction();
        try {
            $updated = $this->requestRepo->updateWhereStatus($id, 'pending', [
                'request_status' => 'approved',
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$updated) {
                Database::rollback();
                return false;
            }

            $this->approvalRepo->log($id, 'approved', $userId, $dto->note);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }

        $this->dispatchStatusNotification((int) $request['created_by'], 'approved', 'คำขอได้รับการอนุมัติ', $request['request_title'], "/requests/{$id}");
        return true;
    }

    /**
     * Reject a pending request (atomic, admin-only).
     */
    public function reject(int $userId, string $role, int $id, ApprovalActionDto $dto): bool
    {
        $request = $this->requestRepo->findById($id);
        if ($request === null) {
            return false;
        }

        if ($request['request_status'] !== 'pending') {
            return false;
        }

        if ($role !== 'admin') {
            return false;
        }

        Database::beginTransaction();
        try {
            $updated = $this->requestRepo->updateWhereStatus($id, 'pending', [
                'request_status' => 'rejected',
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejected_reason' => $dto->note,
            ]);

            if (!$updated) {
                Database::rollback();
                return false;
            }

            $this->approvalRepo->log($id, 'rejected', $userId, $dto->note);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }

        $reason = $dto->note ? " — {$dto->note}" : '';
        $this->dispatchStatusNotification((int) $request['created_by'], 'rejected', 'คำขอถูกปฏิเสธ', $request['request_title'] . $reason, "/requests/{$id}");
        return true;
    }

    private function dispatchSubmitNotifications(int $requestId, array $request, int $userId): void
    {
        try {
            $approvers = Database::query(
                "SELECT user_id FROM approvers WHERE org_id = ? AND is_active = 1",
                [(int) $request['org_id']]
            );
            foreach ($approvers as $approver) {
                $this->notificationService->notify(
                    (int) $approver['user_id'],
                    'approval_request',
                    'มีคำของบประมาณรออนุมัติ',
                    $request['request_title'],
                    "/requests/{$requestId}"
                );
            }
        } catch (\Throwable $e) {
            error_log("[BudgetRequestService::dispatchSubmitNotifications] {$e->getMessage()}");
        }
    }

    private function dispatchStatusNotification(int $userId, string $type, string $title, string $message, string $link): void
    {
        try {
            $this->notificationService->notify($userId, $type, $title, $message, $link);
        } catch (\Throwable $e) {
            error_log("[BudgetRequestService::dispatchStatusNotification] {$e->getMessage()}");
        }
    }
}

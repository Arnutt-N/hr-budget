<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetRequestApprovalRepository
{
    public function log(int $requestId, string $action, int $userId, ?string $note = null): int
    {
        return Database::insert('budget_request_approvals', [
            'budget_request_id' => $requestId,
            'action' => $action,
            'user_id' => $userId,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findByRequestId(int $requestId): array
    {
        return Database::query(
            "SELECT bra.*, u.name as user_name
             FROM budget_request_approvals bra
             LEFT JOIN users u ON bra.user_id = u.id
             WHERE bra.budget_request_id = ?
             ORDER BY bra.created_at DESC",
            [$requestId]
        );
    }
}

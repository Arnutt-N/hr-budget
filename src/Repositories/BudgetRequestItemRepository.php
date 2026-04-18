<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetRequestItemRepository
{
    public function findByRequestId(int $requestId): array
    {
        return Database::query(
            "SELECT * FROM budget_request_items WHERE budget_request_id = ? ORDER BY id",
            [$requestId]
        );
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_request_items', $data);
    }

    public function delete(int $id): bool
    {
        return Database::delete('budget_request_items', 'id = ?', [$id]) > 0;
    }

    public function deleteByRequestId(int $requestId): bool
    {
        return Database::delete('budget_request_items', 'budget_request_id = ?', [$requestId]) > 0;
    }

    /**
     * Replace all items for a request in a single transaction.
     *
     * @param array<array<string,mixed>> $itemRows
     */
    public function replaceItems(int $requestId, array $itemRows): void
    {
        Database::beginTransaction();
        try {
            $this->deleteByRequestId($requestId);
            foreach ($itemRows as $row) {
                $row['budget_request_id'] = $requestId;
                Database::insert('budget_request_items', $row);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }
}

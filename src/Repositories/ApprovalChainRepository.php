<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Data access for the multi-step approval chain: level config + the request's
 * approval state + the per-level action history.
 */
class ApprovalChainRepository
{
    /** @return array<int,array> active levels, ordered */
    public function levels(): array
    {
        return Database::query(
            "SELECT * FROM approval_levels WHERE is_active = 1 ORDER BY level"
        );
    }

    public function levelByNumber(int $level): ?array
    {
        return Database::queryOne(
            "SELECT * FROM approval_levels WHERE level = ? AND is_active = 1",
            [$level]
        );
    }

    public function maxLevel(): int
    {
        $row = Database::queryOne("SELECT MAX(level) AS m FROM approval_levels WHERE is_active = 1");
        return (int) ($row['m'] ?? 0);
    }

    public function findRequest(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM budget_requests WHERE id = ?", [$id]);
    }

    public function updateRequest(int $id, array $data): bool
    {
        $allowed = ['request_status', 'current_level'];
        $clean = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $clean[$f] = $data[$f];
            }
        }
        if ($clean === []) {
            return false;
        }
        return Database::update('budget_requests', $clean, 'id = ?', [$id]) > 0;
    }

    public function recordAction(array $data): int
    {
        return Database::insert('budget_request_approvals', $data);
    }

    /** @return array<int,array> action history (with level) */
    public function historyFor(int $requestId): array
    {
        return Database::query(
            "SELECT * FROM budget_request_approvals WHERE budget_request_id = ? ORDER BY id",
            [$requestId]
        );
    }
}

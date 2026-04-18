<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BudgetRequestRepository
{
    public function findAll(array $filters, int $limit, int $offset): array
    {
        $sql = "SELECT br.*, u.name as created_by_name, o.name_th as org_name
                FROM budget_requests br
                LEFT JOIN users u ON br.created_by = u.id
                LEFT JOIN organizations o ON br.org_id = o.id
                WHERE 1=1";

        $params = $this->applyFilters($sql, $filters);

        $sql .= " ORDER BY br.created_at DESC, br.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    public function count(array $filters): int
    {
        $sql = "SELECT COUNT(*) as total FROM budget_requests br WHERE 1=1";
        $params = $this->applyFilters($sql, $filters);

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT br.*, u.name as created_by_name, o.name_th as org_name
                FROM budget_requests br
                LEFT JOIN users u ON br.created_by = u.id
                LEFT JOIN organizations o ON br.org_id = o.id
                WHERE br.id = ?";

        $result = Database::query($sql, [$id]);
        return $result[0] ?? null;
    }

    public function insert(array $data): int
    {
        return Database::insert('budget_requests', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['fiscal_year', 'request_title', 'request_status', 'total_amount',
            'submitted_at', 'approved_at', 'rejected_at', 'rejected_reason', 'org_id'];

        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return false;
        }

        return Database::update('budget_requests', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('budget_requests', 'id = ?', [$id]) > 0;
    }

    /**
     * @param-out string $sql
     */
    private function applyFilters(string &$sql, array $filters): array
    {
        $params = [];

        if (isset($filters['fiscal_year'])) {
            $sql .= " AND br.fiscal_year = ?";
            $params[] = $filters['fiscal_year'];
        }

        if (isset($filters['status'])) {
            $sql .= " AND br.request_status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['created_by'])) {
            $sql .= " AND br.created_by = ?";
            $params[] = $filters['created_by'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND br.request_title LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }

        return $params;
    }
}

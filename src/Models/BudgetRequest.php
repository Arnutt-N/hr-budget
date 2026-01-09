<?php
/**
 * Budget Request Model
 * 
 * Handles budget request CRUD and workflow
 */

namespace App\Models;

use App\Core\Database;

class BudgetRequest
{
    /**
     * Get all requests with filters
     */
    public static function all(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT br.*, u.name as created_by_name 
                FROM budget_requests br
                LEFT JOIN users u ON br.created_by = u.id
                WHERE 1=1";
        
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

        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND br.request_title LIKE ?";
            $params[] = "%{$filters['search']}%";
        }
        
        $sql .= " ORDER BY br.created_at DESC LIMIT $limit OFFSET $offset";
        
        return Database::query($sql, $params);
    }

    /**
     * Count total requests
     */
    public static function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM budget_requests br WHERE 1=1";
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
        
        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Find request by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT br.*, u.name as created_by_name, u.department
                FROM budget_requests br
                LEFT JOIN users u ON br.created_by = u.id
                WHERE br.id = ?";
        
        $result = Database::query($sql, [$id]);
        return $result[0] ?? null;
    }

    /**
     * Create new request
     */
    public static function create(array $data): int
    {
        return Database::insert('budget_requests', [
            'fiscal_year' => $data['fiscal_year'],
            'request_title' => $data['request_title'],
            'request_status' => $data['request_status'] ?? 'draft',
            'total_amount' => $data['total_amount'] ?? 0,
            'created_by' => $data['created_by'],
            'org_id' => $data['org_id'] ?? null, // Phase 3
        ]);
    }

    /**
     * Update request
     */
    public static function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowedFields = ['fiscal_year', 'request_title', 'request_status', 'total_amount', 'submitted_at', 'approved_at', 'rejected_at', 'rejected_reason'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return Database::update('budget_requests', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete request
     */
    public static function delete(int $id): bool
    {
        return Database::delete('budget_requests', 'id = ?', [$id]) > 0;
    }

    /**
     * Update status
     */
    public static function updateStatus(int $id, string $status, ?string $reason = null): bool
    {
        $data = ['request_status' => $status];
        
        if ($status === 'pending') {
            $data['submitted_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'approved') {
            $data['approved_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = date('Y-m-d H:i:s');
            $data['rejected_reason'] = $reason;
        }

        return self::update($id, $data);
    }

    /**
     * Get statistics for dashboard
     */
    public static function getStats(?int $fiscalYear = null): array
    {
        $db = Database::getPdo();
        $params = [];
        $where = "";
        
        if ($fiscalYear) {
            $where = "WHERE fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN request_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN request_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN request_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(total_amount) as total_amount,
                    SUM(CASE WHEN request_status = 'approved' THEN total_amount ELSE 0 END) as approved_amount
                FROM budget_requests 
                $where";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent requests for dashboard
     */
    public static function getRecentRequests(int $limit = 5): array
    {
        $db = Database::getPdo();
        $sql = "SELECT r.*, u.name as created_by_name 
                FROM budget_requests r
                LEFT JOIN users u ON r.created_by = u.id
                ORDER BY r.created_at DESC, r.id DESC 
                LIMIT ?";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

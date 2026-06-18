<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class DisbursementSessionRepository
{
    /**
     * @param array<string,mixed> $filters
     * @return array<int,array<string,mixed>>
     */
    public function findAll(array $filters, int $limit, int $offset): array
    {
        $sql = "SELECT ds.*, o.name_th AS organization_name
                FROM disbursement_sessions ds
                LEFT JOIN organizations o ON ds.organization_id = o.id
                WHERE 1=1";

        $params = $this->applyFilters($sql, $filters);

        $sql .= " ORDER BY ds.fiscal_year DESC, ds.record_month DESC, ds.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::query($sql, $params);
    }

    /**
     * @param array<string,mixed> $filters
     */
    public function count(array $filters): int
    {
        $sql = "SELECT COUNT(*) AS total FROM disbursement_sessions ds WHERE 1=1";
        $params = $this->applyFilters($sql, $filters);

        $result = Database::query($sql, $params);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT ds.*, o.name_th AS organization_name
             FROM disbursement_sessions ds
             LEFT JOIN organizations o ON ds.organization_id = o.id
             WHERE ds.id = ?",
            [$id]
        );
    }

    public function findByOrgYearMonth(int $organizationId, int $fiscalYear, int $recordMonth): ?array
    {
        return Database::queryOne(
            "SELECT * FROM disbursement_sessions
             WHERE organization_id = ? AND fiscal_year = ? AND record_month = ?",
            [$organizationId, $fiscalYear, $recordMonth]
        );
    }

    /**
     * @param array{organization_id:int,fiscal_year:int,record_month:int,record_date:string,created_by:?int} $data
     */
    public function insert(array $data): int
    {
        return Database::insert('disbursement_sessions', $data);
    }

    /**
     * Activities available for this session.
     *
     * If source_of_truth_mappings has official rows for (org, fy), only those
     * activities are returned; otherwise all active activities for the fiscal
     * year. record_id / record_status expose whether a record already exists.
     *
     * @return array<int,array<string,mixed>>
     */
    public function activitiesForSession(int $orgId, int $fiscalYear, int $sessionId): array
    {
        $official = Database::query(
            "SELECT activity_id FROM source_of_truth_mappings
             WHERE organization_id = ? AND fiscal_year = ? AND is_official = 1",
            [$orgId, $fiscalYear]
        );

        $sql = "SELECT a.id AS activity_id, a.code, a.name_th,
                       p.name_th AS project_name, pl.name_th AS plan_name,
                       dr.id AS record_id, dr.status AS record_status
                FROM activities a
                LEFT JOIN projects p ON a.project_id = p.id
                LEFT JOIN plans pl ON p.plan_id = pl.id
                LEFT JOIN disbursement_records dr
                       ON dr.activity_id = a.id AND dr.session_id = ?
                WHERE a.is_active = 1 AND a.deleted_at IS NULL";
        $params = [$sessionId];

        if ($official !== []) {
            $ids = array_map(static fn ($row) => (int) $row['activity_id'], $official);
            $placeholders = implode(', ', array_fill(0, count($ids), '?'));
            $sql .= " AND a.id IN ({$placeholders})";
            $params = array_merge($params, $ids);
        } else {
            $sql .= " AND a.fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        $sql .= " ORDER BY a.sort_order, a.id";

        return Database::query($sql, $params);
    }

    /**
     * Cascade-delete a session and all of its records + trackings.
     * Subquery-form DELETEs are portable to SQLite. Wrap in a transaction
     * at the service layer.
     */
    public function deleteCascade(int $sessionId): void
    {
        Database::delete(
            'budget_trackings',
            'disbursement_record_id IN (SELECT id FROM disbursement_records WHERE session_id = ?)',
            [$sessionId]
        );
        Database::delete('disbursement_records', 'session_id = ?', [$sessionId]);
        Database::delete('disbursement_sessions', 'id = ?', [$sessionId]);
    }

    /**
     * @param string &$sql
     * @param array<string,mixed> $filters
     * @return array<int,int>
     */
    private function applyFilters(string &$sql, array $filters): array
    {
        $params = [];

        if (isset($filters['fiscal_year'])) {
            $sql .= " AND ds.fiscal_year = ?";
            $params[] = $filters['fiscal_year'];
        }

        if (isset($filters['organization_id'])) {
            $sql .= " AND ds.organization_id = ?";
            $params[] = $filters['organization_id'];
        }

        // RBAC additive scope (Phase 10): constrain to the set of org ids the
        // viewer may read. An empty set denies all rows (1=0) — never an empty
        // IN(), which is a SQL syntax error.
        if (isset($filters['organization_ids'])) {
            $ids = array_values($filters['organization_ids']);
            if ($ids === []) {
                $sql .= " AND 1=0";
            } else {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql .= " AND ds.organization_id IN ($placeholders)";
                foreach ($ids as $id) {
                    $params[] = $id;
                }
            }
        }

        if (isset($filters['record_month'])) {
            $sql .= " AND ds.record_month = ?";
            $params[] = $filters['record_month'];
        }

        return $params;
    }
}

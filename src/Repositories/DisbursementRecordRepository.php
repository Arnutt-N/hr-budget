<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Disbursement records (per session + activity) and their budget_trackings rows.
 *
 * The tracking upsert is intentionally written as a portable SELECT-then-
 * update/insert keyed on (disbursement_record_id, expense_item_id) so the
 * exact same code path runs under both MySQL (prod) and SQLite (unit tests).
 * MySQL `ON DUPLICATE KEY UPDATE` is deliberately NOT used.
 */
class DisbursementRecordRepository
{
    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT id, session_id, activity_id, status, created_at, updated_at
             FROM disbursement_records
             WHERE id = ?",
            [$id]
        );
    }

    public function findBySessionAndActivity(int $sessionId, int $activityId): ?array
    {
        return Database::queryOne(
            "SELECT id, session_id, activity_id, status, created_at, updated_at
             FROM disbursement_records
             WHERE session_id = ? AND activity_id = ?",
            [$sessionId, $activityId]
        );
    }

    /**
     * Insert a new record (status defaults to 'draft').
     */
    public function insert(int $sessionId, int $activityId): int
    {
        return Database::insert('disbursement_records', [
            'session_id' => $sessionId,
            'activity_id' => $activityId,
            'status' => 'draft',
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Database::update(
            'disbursement_records',
            ['status' => $status],
            'id = ?',
            [$id]
        ) > 0;
    }

    /**
     * All tracking rows for a record (raw; the service keys them by item).
     *
     * @return array<int,array<string,mixed>>
     */
    public function trackingsByRecord(int $recordId): array
    {
        return Database::query(
            "SELECT expense_item_id, expense_group_id, expense_type_id,
                    allocated, transfer, disbursed, pending, po
             FROM budget_trackings
             WHERE disbursement_record_id = ?
             ORDER BY expense_item_id",
            [$recordId]
        );
    }

    /**
     * Portable upsert keyed on (disbursement_record_id, expense_item_id).
     *
     * On insert, only the columns relevant to the tracking wizard are set;
     * budget_category_item_id / budget_type_id / plan_id / project_id stay
     * NULL (matches seed row 57 and avoids the unique_tracking
     * (fiscal_year, budget_category_item_id) collision across records).
     *
     * @param array{
     *   disbursement_record_id:int, activity_id:int,
     *   expense_type_id:?int, expense_group_id:?int, expense_item_id:int,
     *   fiscal_year:int, record_month:?int, organization_id:?int,
     *   allocated:string, transfer:string, disbursed:string, pending:string, po:string
     * } $row
     */
    public function upsertTracking(array $row): void
    {
        $existing = Database::queryOne(
            "SELECT id FROM budget_trackings
             WHERE disbursement_record_id = ? AND expense_item_id = ?",
            [$row['disbursement_record_id'], $row['expense_item_id']]
        );

        if ($existing !== null) {
            Database::update(
                'budget_trackings',
                [
                    'allocated' => $row['allocated'],
                    'transfer' => $row['transfer'],
                    'disbursed' => $row['disbursed'],
                    'pending' => $row['pending'],
                    'po' => $row['po'],
                ],
                'id = ?',
                [(int) $existing['id']]
            );
            return;
        }

        Database::insert('budget_trackings', [
            'disbursement_record_id' => $row['disbursement_record_id'],
            'activity_id' => $row['activity_id'],
            'expense_type_id' => $row['expense_type_id'],
            'expense_group_id' => $row['expense_group_id'],
            'expense_item_id' => $row['expense_item_id'],
            'fiscal_year' => $row['fiscal_year'],
            'record_month' => $row['record_month'],
            'organization_id' => $row['organization_id'],
            'allocated' => $row['allocated'],
            'transfer' => $row['transfer'],
            'disbursed' => $row['disbursed'],
            'pending' => $row['pending'],
            'po' => $row['po'],
        ]);
    }
}

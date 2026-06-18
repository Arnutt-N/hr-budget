<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateDisbursementRecordDto;
use App\Dtos\CreateDisbursementSessionDto;
use App\Dtos\DisbursementSessionListQueryDto;
use App\Dtos\SaveTrackingItemsDto;
use App\Repositories\DisbursementRecordRepository;
use App\Repositories\DisbursementSessionRepository;
use App\Repositories\ExpenseStructureRepository;

/**
 * Orchestrates the monthly disbursement tracking flow:
 *   session (org + fy + month) → activities → record (per activity) → trackings.
 *
 * Role/org gating: non-admin users may only act within their own organization,
 * so the client-supplied organization_id is ignored for them (security control).
 * All multi-write operations run inside a DB transaction.
 */
final class DisbursementService
{
    /** decimal(15,2) money columns → bc math scale. */
    private const MONEY_SCALE = 2;

    public function __construct(
        private readonly DisbursementSessionRepository $sessionRepo = new DisbursementSessionRepository(),
        private readonly DisbursementRecordRepository $recordRepo = new DisbursementRecordRepository(),
        private readonly ExpenseStructureRepository $expenseRepo = new ExpenseStructureRepository(),
        private readonly AccessScopeResolver $scopeResolver = new AccessScopeResolver(),
    ) {}

    /**
     * Org ids a non-admin user may READ (Phase 10 additive scope): their own org
     * UNION any org inside a granted subtree. Returns null when there is no
     * restriction at all (super admin, or an org-wide 'all' grant). Returns an
     * empty array when the user may read nothing.
     *
     * READ-only — write paths keep the stricter own-org rule (resolveOrgId /
     * canAccessOrg) so a subtree viewer can see but not mutate child-org data.
     *
     * @param array<string,mixed> $user
     * @return array<int,int>|null
     */
    private function readableOrgIds(string $role, array $user): ?array
    {
        if ($role === 'admin') {
            return null;
        }

        $scope = $this->scopeResolver->resolve($user);
        if ($scope['hasAll']) {
            return null;
        }

        $ids = $scope['orgIds'];
        $ownOrg = (int) ($user['org_id'] ?? $user['organization_id'] ?? 0);
        if ($ownOrg > 0) {
            $ids[] = $ownOrg;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Object-level READ guard mirroring readableOrgIds(): true when the target
     * org is within the user's readable set (own org ∪ granted subtree), or when
     * there is no restriction (admin / 'all' grant).
     *
     * @param array<string,mixed> $user
     */
    private function canReadOrg(string $role, array $user, int $targetOrgId): bool
    {
        $readable = $this->readableOrgIds($role, $user);
        if ($readable === null) {
            return true;
        }

        return in_array($targetOrgId, $readable, true);
    }

    /**
     * Resolve the organization a non-admin user is allowed to act on.
     * Admins keep whatever org they passed.
     */
    private function resolveOrgId(string $role, array $user, int $requestedOrgId): int
    {
        if ($role === 'admin') {
            return $requestedOrgId;
        }

        // Non-admin: force their own org regardless of the request body.
        return (int) ($user['org_id'] ?? $user['organization_id'] ?? 0);
    }

    /**
     * Object-level ownership guard (BOLA/IDOR defense).
     *
     * Admins may act on any organization. A non-admin may only act on rows
     * belonging to their own organization; a non-admin with no resolvable org
     * (own org 0) is always denied. This is the WRITE guard (own-org only) used
     * by create/save paths; READ paths use canReadOrg (subtree-aware, Phase 10).
     */
    private function canAccessOrg(string $role, array $user, int $targetOrgId): bool
    {
        if ($role === 'admin') {
            return true;
        }

        $ownOrg = (int) ($user['org_id'] ?? $user['organization_id'] ?? 0);
        if ($ownOrg <= 0) {
            return false;
        }

        return $targetOrgId === $ownOrg;
    }

    /**
     * List sessions with filters + pagination. Non-admin is scoped to own org.
     *
     * @return array{data: array<int,array<string,mixed>>, meta: array{total:int,page:int,per_page:int,total_pages:int}}
     */
    public function listSessions(string $role, array $user, DisbursementSessionListQueryDto $query): array
    {
        $filters = $query->toFilters();

        // Phase 10: scope reads to the viewer's own org ∪ granted subtree
        // (additive). null = no restriction (admin / 'all'); [] = deny-all.
        $readable = $this->readableOrgIds($role, $user);
        if ($readable !== null) {
            $filters['organization_ids'] = $readable;
        }

        $total = $this->sessionRepo->count($filters);
        $data = $this->sessionRepo->findAll($filters, $query->perPage, $query->offset());

        return [
            'data' => array_map([$this, 'shapeSession'], $data),
            'meta' => [
                'total' => $total,
                'page' => $query->page,
                'per_page' => $query->perPage,
                'total_pages' => $query->perPage > 0 ? (int) ceil($total / $query->perPage) : 0,
            ],
        ];
    }

    /**
     * Create-or-fetch a session for (org, fy, month). Idempotent.
     * Non-admin org is forced to the user's own org.
     */
    public function createOrFetchSession(string $role, array $user, CreateDisbursementSessionDto $dto): ?array
    {
        $orgId = $this->resolveOrgId($role, $user, $dto->organizationId);
        if ($orgId <= 0) {
            return null;
        }

        $existing = $this->sessionRepo->findByOrgYearMonth($orgId, $dto->fiscalYear, $dto->recordMonth);
        if ($existing !== null) {
            // orgId is already the caller's authorized org here; load directly.
            return $this->loadSession((int) $existing['id']);
        }

        $id = $this->sessionRepo->insert([
            'organization_id' => $orgId,
            'fiscal_year' => $dto->fiscalYear,
            'record_month' => $dto->recordMonth,
            'record_date' => $dto->recordDate,
            'created_by' => (int) ($user['id'] ?? 0),
        ]);

        return $this->loadSession($id);
    }

    /**
     * Fetch a single session. Non-admin may read sessions in their own org or a
     * granted subtree (Phase 10). Returns null when the session is missing OR
     * access is denied (callers map null → notFound, never leaking existence).
     */
    public function getSession(string $role, array $user, int $id): ?array
    {
        $session = $this->sessionRepo->findById($id);
        if ($session === null) {
            return null;
        }

        if (!$this->canReadOrg($role, $user, (int) $session['organization_id'])) {
            return null;
        }

        return $this->shapeSession($session);
    }

    /** Internal load without ownership guard (caller already authorized org). */
    private function loadSession(int $id): ?array
    {
        $session = $this->sessionRepo->findById($id);
        return $session !== null ? $this->shapeSession($session) : null;
    }

    /**
     * Delete a session and its records/trackings (atomic).
     * Non-admin must own the session's organization.
     */
    public function deleteSession(string $role, array $user, int $id): bool
    {
        $session = $this->sessionRepo->findById($id);
        if ($session === null) {
            return false;
        }

        if ($role !== 'admin') {
            $ownOrg = (int) ($user['org_id'] ?? $user['organization_id'] ?? 0);
            if ((int) $session['organization_id'] !== $ownOrg) {
                return false;
            }
        }

        Database::beginTransaction();
        try {
            $this->sessionRepo->deleteCascade($id);
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollback();
            error_log("[DisbursementService::deleteSession] {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Activities selectable for this session (with has-record markers).
     * Non-admin may list activities for a session in their own org or a granted
     * subtree (Phase 10).
     *
     * @return array<int,array<string,mixed>>|null null if session missing or denied
     */
    public function getActivities(string $role, array $user, int $sessionId): ?array
    {
        $session = $this->sessionRepo->findById($sessionId);
        if ($session === null) {
            return null;
        }

        if (!$this->canReadOrg($role, $user, (int) $session['organization_id'])) {
            return null;
        }

        $rows = $this->sessionRepo->activitiesForSession(
            (int) $session['organization_id'],
            (int) $session['fiscal_year'],
            $sessionId
        );

        return array_map([$this, 'shapeActivity'], $rows);
    }

    /**
     * Create-or-fetch a record for (session, activity). Idempotent.
     * Returns null when the session does not exist or ownership is denied
     * (non-admin: the target session must belong to the caller's own org).
     */
    public function createOrFetchRecord(string $role, array $user, CreateDisbursementRecordDto $dto): ?array
    {
        $session = $this->sessionRepo->findById($dto->sessionId);
        if ($session === null) {
            return null;
        }

        if (!$this->canAccessOrg($role, $user, (int) $session['organization_id'])) {
            return null;
        }

        $existing = $this->recordRepo->findBySessionAndActivity($dto->sessionId, $dto->activityId);
        if ($existing !== null) {
            return $this->shapeRecord($existing);
        }

        $id = $this->recordRepo->insert($dto->sessionId, $dto->activityId);
        $record = $this->recordRepo->findById($id);
        return $record !== null ? $this->shapeRecord($record) : null;
    }

    /**
     * Full record detail: record + session + activity + existing tracking
     * amounts keyed by expense_item_id (each with computed `remaining`).
     * Non-admin may read a record whose parent session is in their own org or a
     * granted subtree (Phase 10). Returns null when missing OR access is denied.
     */
    public function getRecordDetail(string $role, array $user, int $recordId): ?array
    {
        $record = $this->recordRepo->findById($recordId);
        if ($record === null) {
            return null;
        }

        $session = $this->sessionRepo->findById((int) $record['session_id']);
        if ($session === null) {
            return null;
        }

        if (!$this->canReadOrg($role, $user, (int) $session['organization_id'])) {
            return null;
        }

        return $this->buildRecordDetail($record, $session);
    }

    /**
     * Assemble the record-detail payload from already-fetched rows.
     * No ownership guard — callers must have authorized access first.
     *
     * @param array<string,mixed> $record
     * @param array<string,mixed> $session
     */
    private function buildRecordDetail(array $record, array $session): array
    {
        $recordId = (int) $record['id'];
        $activity = $this->findActivity((int) $record['activity_id']);

        $trackings = [];
        foreach ($this->recordRepo->trackingsByRecord($recordId) as $row) {
            $itemId = (int) $row['expense_item_id'];
            $trackings[$itemId] = $this->shapeTracking($row);
        }

        return [
            'record' => $this->shapeRecord($record),
            'session' => $session !== null ? $this->shapeSession($session) : null,
            'activity' => $activity,
            'trackings' => $trackings,
        ];
    }

    /**
     * Upsert all tracking amounts for a record, then mark it completed (atomic).
     * Non-admin may only save a record whose parent session is own-org.
     * Returns the refreshed detail on success, null on failure/denied.
     */
    public function saveRecordItems(string $role, array $user, int $recordId, SaveTrackingItemsDto $dto): ?array
    {
        $record = $this->recordRepo->findById($recordId);
        if ($record === null) {
            return null;
        }

        $session = $this->sessionRepo->findById((int) $record['session_id']);
        if ($session === null) {
            return null;
        }

        if (!$this->canAccessOrg($role, $user, (int) $session['organization_id'])) {
            return null;
        }

        $fiscalYear = (int) $session['fiscal_year'];
        $recordMonth = isset($session['record_month']) ? (int) $session['record_month'] : null;
        $organizationId = isset($session['organization_id']) ? (int) $session['organization_id'] : null;
        $activityId = (int) $record['activity_id'];

        // Batch-resolve every expense item up front (single IN(...) query)
        // instead of one query per item inside the loop (N+1 fix). If any id
        // does not resolve, abort exactly as the per-item path used to.
        $itemIds = array_map(static fn ($item) => $item->expenseItemId, $dto->items);
        $resolvedMap = $this->expenseRepo->resolveItems($itemIds);

        Database::beginTransaction();
        try {
            foreach ($dto->items as $item) {
                $resolved = $resolvedMap[$item->expenseItemId] ?? null;
                if ($resolved === null) {
                    Database::rollback();
                    return null;
                }

                $amounts = $item->amounts();
                $this->recordRepo->upsertTracking([
                    'disbursement_record_id' => $recordId,
                    'activity_id' => $activityId,
                    'expense_type_id' => $resolved['expense_type_id'],
                    'expense_group_id' => $resolved['expense_group_id'],
                    'expense_item_id' => $item->expenseItemId,
                    'fiscal_year' => $fiscalYear,
                    'record_month' => $recordMonth,
                    'organization_id' => $organizationId,
                    'allocated' => $amounts['allocated'],
                    'transfer' => $amounts['transfer'],
                    'disbursed' => $amounts['disbursed'],
                    'pending' => $amounts['pending'],
                    'po' => $amounts['po'],
                ]);
            }

            $this->recordRepo->updateStatus($recordId, 'completed');
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            error_log("[DisbursementService::saveRecordItems] {$e->getMessage()}");
            return null;
        }

        // Re-read the record so its now-'completed' status is reflected.
        $fresh = $this->recordRepo->findById($recordId);
        if ($fresh === null) {
            return null;
        }

        return $this->buildRecordDetail($fresh, $session);
    }

    /**
     * Reference tree of expense types → groups → items.
     *
     * @return array<int,array<string,mixed>>
     */
    public function expenseStructure(): array
    {
        return array_map([$this, 'shapeExpenseType'], $this->expenseRepo->tree());
    }

    // ---- shaping helpers (normalize DB rows to the TS contract) ----

    /** remaining = allocated + transfer - (disbursed + pending + po). */
    private function calcRemaining(string $allocated, string $transfer, string $disbursed, string $pending, string $po): string
    {
        $inflow = bcadd($allocated, $transfer, self::MONEY_SCALE);
        $outflow = bcadd(bcadd($disbursed, $pending, self::MONEY_SCALE), $po, self::MONEY_SCALE);
        return bcsub($inflow, $outflow, self::MONEY_SCALE);
    }

    private function money(mixed $value): string
    {
        $str = $value === null ? '0' : (string) $value;
        return is_numeric($str) ? bcadd($str, '0', self::MONEY_SCALE) : '0.00';
    }

    /** @param array<string,mixed> $row */
    private function shapeSession(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'organization_id' => (int) $row['organization_id'],
            'fiscal_year' => (int) $row['fiscal_year'],
            'record_month' => (int) $row['record_month'],
            'record_date' => (string) $row['record_date'],
            'created_by' => $row['created_by'] !== null ? (int) $row['created_by'] : null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'org_name' => $row['organization_name'] ?? null,
        ];
    }

    /** @param array<string,mixed> $row */
    private function shapeActivity(array $row): array
    {
        return [
            'activity_id' => (int) $row['activity_id'],
            'code' => $row['code'] ?? null,
            'name_th' => (string) $row['name_th'],
            'plan_name' => $row['plan_name'] ?? null,
            'project_name' => $row['project_name'] ?? null,
            'record_id' => isset($row['record_id']) && $row['record_id'] !== null ? (int) $row['record_id'] : null,
            'record_status' => $row['record_status'] ?? null,
        ];
    }

    private function findActivity(int $activityId): ?array
    {
        $row = Database::queryOne(
            "SELECT a.id AS activity_id, a.code, a.name_th,
                    p.name_th AS project_name, pl.name_th AS plan_name
             FROM activities a
             LEFT JOIN projects p ON a.project_id = p.id
             LEFT JOIN plans pl ON p.plan_id = pl.id
             WHERE a.id = ?",
            [$activityId]
        );

        if ($row === null) {
            return null;
        }

        return [
            'activity_id' => (int) $row['activity_id'],
            'code' => $row['code'] ?? null,
            'name_th' => (string) $row['name_th'],
            'plan_name' => $row['plan_name'] ?? null,
            'project_name' => $row['project_name'] ?? null,
            'record_id' => null,
            'record_status' => null,
        ];
    }

    /** @param array<string,mixed> $row */
    private function shapeRecord(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'session_id' => (int) $row['session_id'],
            'activity_id' => (int) $row['activity_id'],
            'status' => (string) $row['status'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    /** @param array<string,mixed> $row */
    private function shapeTracking(array $row): array
    {
        $allocated = $this->money($row['allocated'] ?? '0');
        $transfer = $this->money($row['transfer'] ?? '0');
        $disbursed = $this->money($row['disbursed'] ?? '0');
        $pending = $this->money($row['pending'] ?? '0');
        $po = $this->money($row['po'] ?? '0');

        return [
            'expense_item_id' => (int) $row['expense_item_id'],
            'allocated' => $allocated,
            'transfer' => $transfer,
            'disbursed' => $disbursed,
            'pending' => $pending,
            'po' => $po,
            'remaining' => $this->calcRemaining($allocated, $transfer, $disbursed, $pending, $po),
        ];
    }

    /** @param array<string,mixed> $type */
    private function shapeExpenseType(array $type): array
    {
        $groups = array_map(function (array $group): array {
            $items = array_map(static function (array $item): array {
                return [
                    'id' => (int) $item['id'],
                    'expense_group_id' => $item['expense_group_id'] !== null ? (int) $item['expense_group_id'] : null,
                    'expense_type_id' => $item['expense_type_id'] !== null ? (int) $item['expense_type_id'] : null,
                    'code' => $item['code'] ?? null,
                    'name_th' => (string) $item['name_th'],
                    'level' => (int) ($item['level'] ?? 0),
                    'is_header' => (int) ($item['is_header'] ?? 0),
                    'is_active' => 1,
                ];
            }, $group['items'] ?? []);

            return [
                'id' => (int) $group['id'],
                'expense_type_id' => (int) $group['expense_type_id'],
                'code' => $group['code'] ?? null,
                'name_th' => (string) $group['name_th'],
                'items' => $items,
            ];
        }, $type['groups'] ?? []);

        return [
            'id' => (int) $type['id'],
            'code' => $type['code'] ?? null,
            'name_th' => (string) $type['name_th'],
            'groups' => $groups,
        ];
    }
}

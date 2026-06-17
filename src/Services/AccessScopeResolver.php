<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\AccessGrantRepository;

/**
 * Security core for RBAC + multi-org scoping.
 *
 * Given an authenticated user, resolves the union of their permissions and the
 * set of organization ids they may see. Organization scope is expanded to the
 * whole subtree (org + all descendants) via a single recursive CTE.
 *
 * Phase 1 enforces 'organization' and 'all' scope. 'category' and 'region'
 * refs are resolved here but enforcement is deferred to Phase 3 (when budget
 * data and its classification columns are populated).
 */
final class AccessScopeResolver
{
    public function __construct(
        private readonly AccessGrantRepository $grants = new AccessGrantRepository(),
    ) {}

    /**
     * @param array<string,mixed> $user  authenticated user array (id, role, ...)
     * @return array{
     *   isSuperAdmin: bool, hasAll: bool, permissions: array<int,string>,
     *   orgIds: array<int,int>, categoryIds: array<int,int>, regionRefs: array<int,int>
     * }
     */
    public function resolve(array $user): array
    {
        // Legacy global admin = super admin: full bypass, no queries.
        if (($user['role'] ?? '') === 'admin') {
            return [
                'isSuperAdmin' => true,
                'hasAll' => true,
                'permissions' => ['*'],
                'orgIds' => [],
                'categoryIds' => [],
                'regionRefs' => [],
            ];
        }

        $userId = (int) ($user['id'] ?? 0);
        $grants = $userId > 0 ? $this->grants->findActiveByUser($userId) : [];
        $permissions = $userId > 0 ? $this->grants->permissionCodesForUser($userId) : [];

        $hasAll = false;
        $rootOrgIds = [];
        $categoryIds = [];
        $regionRefs = [];

        foreach ($grants as $g) {
            $ref = $g['scope_ref_id'] !== null ? (int) $g['scope_ref_id'] : null;
            switch ($g['scope_type']) {
                case 'all':
                    $hasAll = true;
                    break;
                case 'organization':
                    if ($ref !== null) {
                        $rootOrgIds[] = $ref;
                    }
                    break;
                case 'category':
                    if ($ref !== null) {
                        $categoryIds[] = $ref;
                    }
                    break;
                case 'region':
                    if ($ref !== null) {
                        $regionRefs[] = $ref;
                    }
                    break;
            }
        }

        return [
            'isSuperAdmin' => false,
            'hasAll' => $hasAll,
            'permissions' => array_values(array_unique($permissions)),
            'orgIds' => $hasAll ? [] : $this->expandDescendants($rootOrgIds),
            'categoryIds' => array_values(array_unique($categoryIds)),
            'regionRefs' => array_values(array_unique($regionRefs)),
        ];
    }

    /**
     * Expand a set of organization ids to include all descendants (subtree),
     * using a single recursive CTE over organizations.parent_id.
     *
     * @param array<int,int> $rootIds
     * @return array<int,int>
     */
    public function expandDescendants(array $rootIds): array
    {
        $rootIds = array_values(array_unique(array_map('intval', $rootIds)));
        if ($rootIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($rootIds), '?'));
        $rows = Database::query(
            "WITH RECURSIVE subtree AS (
                 SELECT id, parent_id FROM organizations WHERE id IN ($placeholders)
                 UNION ALL
                 SELECT o.id, o.parent_id FROM organizations o
                 JOIN subtree s ON o.parent_id = s.id
             )
             SELECT DISTINCT id FROM subtree",
            $rootIds
        );
        return array_map(static fn ($r) => (int) $r['id'], $rows);
    }

    /** True if the user holds the given permission (super admin always true). */
    public function can(array $user, string $permission): bool
    {
        $scope = $this->resolve($user);
        if ($scope['isSuperAdmin']) {
            return true;
        }
        return in_array($permission, $scope['permissions'], true);
    }

    /**
     * Build a SQL fragment + params that constrain a query to the user's
     * organization scope. Returns ['sql' => '...', 'params' => [...]].
     *
     * - super admin / scope=all  → no constraint ('1=1')
     * - has org scope            → "<column> IN (?, ?, ...)"
     * - no org access            → "1=0" (deny-all; nothing visible)
     *
     * @param array<string,mixed> $user
     */
    public function orgScopeFilter(array $user, string $column = 'organization_id'): array
    {
        $scope = $this->resolve($user);
        if ($scope['isSuperAdmin'] || $scope['hasAll']) {
            return ['sql' => '1=1', 'params' => []];
        }
        $orgIds = $scope['orgIds'];
        if ($orgIds === []) {
            return ['sql' => '1=0', 'params' => []];
        }
        $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
        return ['sql' => "$column IN ($placeholders)", 'params' => $orgIds];
    }
}

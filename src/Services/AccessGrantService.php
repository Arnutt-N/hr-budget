<?php
declare(strict_types=1);

namespace App\Services;

use App\Dtos\AssignGrantDto;
use App\Repositories\AccessGrantRepository;
use App\Repositories\RoleRepository;

/**
 * Assigns / revokes role-scope grants to users, with privilege-escalation
 * guards so a non-super actor cannot grant access beyond their own reach.
 */
final class AccessGrantService
{
    public function __construct(
        private readonly AccessGrantRepository $grants = new AccessGrantRepository(),
        private readonly RoleRepository $roles = new RoleRepository(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
    ) {}

    /** @return array<int,array> */
    public function listForUser(int $targetUserId): array
    {
        return $this->grants->findByUser($targetUserId);
    }

    /**
     * @param array<string,mixed> $actor   the authenticated user performing the action
     * @return array{ok:bool,id?:int,error?:string}
     */
    public function assign(array $actor, int $targetUserId, AssignGrantDto $dto): array
    {
        $role = $this->roles->findById($dto->roleId);
        if ($role === null) {
            return ['ok' => false, 'error' => 'role_not_found'];
        }
        if ((int) $role['is_active'] !== 1) {
            return ['ok' => false, 'error' => 'role_inactive'];
        }

        $actorScope = $this->resolver->resolve($actor);
        $isSuper = $actorScope['isSuperAdmin'];

        // Escalation guard: only super admin may grant privileged / system roles
        // or the global 'all' scope.
        $rolePerms = $this->roles->getPermissionCodes($dto->roleId);
        $privileged = (int) $role['is_system'] === 1
            || in_array('role.manage', $rolePerms, true)
            || in_array('org.manage', $rolePerms, true);
        if (!$isSuper && $privileged) {
            return ['ok' => false, 'error' => 'forbidden_privileged_role'];
        }
        if (!$isSuper && $dto->scopeType === 'all') {
            return ['ok' => false, 'error' => 'forbidden_all_scope'];
        }

        // Non-super actor may only grant organization scope inside their own subtree.
        if (!$isSuper && $dto->scopeType === 'organization') {
            if (!in_array((int) $dto->scopeRefId, $actorScope['orgIds'], true)) {
                return ['ok' => false, 'error' => 'forbidden_out_of_scope'];
            }
        }
        // category/region grants are super-admin-only until Phase 3 wiring.
        if (!$isSuper && in_array($dto->scopeType, ['category', 'region'], true)) {
            return ['ok' => false, 'error' => 'forbidden_scope_type'];
        }

        try {
            $id = $this->grants->insert([
                'user_id' => $targetUserId,
                'role_id' => $dto->roleId,
                'scope_type' => $dto->scopeType,
                'scope_ref_id' => $dto->scopeRefId,
                'is_active' => 1,
                'created_by' => (int) ($actor['id'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            // unique(user_id, role_id, scope_type, scope_ref_id) violation
            return ['ok' => false, 'error' => 'duplicate_grant'];
        }

        return ['ok' => true, 'id' => $id];
    }

    /**
     * @param array<string,mixed> $actor
     * @return array{ok:bool,error?:string}
     */
    public function revoke(array $actor, int $grantId): array
    {
        $grant = $this->grants->findById($grantId);
        if ($grant === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }

        $actorScope = $this->resolver->resolve($actor);
        if (!$actorScope['isSuperAdmin']) {
            // Non-super may only revoke org-scoped grants inside their subtree.
            if ($grant['scope_type'] !== 'organization'
                || !in_array((int) $grant['scope_ref_id'], $actorScope['orgIds'], true)) {
                return ['ok' => false, 'error' => 'forbidden_out_of_scope'];
            }
        }

        return ['ok' => $this->grants->delete($grantId)];
    }
}

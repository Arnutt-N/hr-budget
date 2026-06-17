<?php
declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Repositories\PermissionRepository;
use App\Services\AccessScopeResolver;

/**
 * GET /api/v1/me/permissions — effective permissions + org scope of the
 * current user, so the SPA can drive button/menu visibility.
 */
final class MeController
{
    public function __construct(
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
        private readonly PermissionRepository $permissions = new PermissionRepository(),
    ) {}

    public function permissions(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        $scope = $this->resolver->resolve($user);

        // Expand super-admin wildcard to the concrete catalogue for the UI.
        $perms = $scope['permissions'];
        if ($scope['isSuperAdmin']) {
            $perms = array_map(static fn ($p) => $p['code'], $this->permissions->findAll());
        }

        ApiResponse::ok([
            'user_id' => (int) ($user['id'] ?? 0),
            'is_super_admin' => $scope['isSuperAdmin'],
            'has_all_orgs' => $scope['hasAll'],
            'permissions' => $perms,
            'organization_ids' => $scope['orgIds'],
        ]);
    }
}

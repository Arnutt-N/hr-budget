<?php
declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Services\AccessScopeResolver;
use App\Services\RoleService;

final class PermissionController
{
    public function __construct(
        private readonly RoleService $service = new RoleService(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
    ) {}

    /** GET /api/v1/permissions — the fixed permission catalogue. */
    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์ดูรายการสิทธิ์');
            return;
        }
        ApiResponse::ok($this->service->listPermissions());
    }
}

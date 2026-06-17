<?php
declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateRoleDto;
use App\Dtos\UpdateRoleDto;
use App\Services\AccessScopeResolver;
use App\Services\RoleService;

final class RoleController
{
    public function __construct(
        private readonly RoleService $service = new RoleService(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการบทบาท');
            return;
        }
        ApiResponse::ok($this->service->list());
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการบทบาท');
            return;
        }
        $role = $this->service->findById((int) $id);
        if ($role === null) {
            ApiResponse::notFound('ไม่พบบทบาท');
            return;
        }
        ApiResponse::ok($role);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการบทบาท');
            return;
        }
        try {
            $dto = CreateRoleDto::fromRequest();
            $errors = $dto->validate();
            if ($errors !== []) {
                ApiResponse::validationFailed($errors);
                return;
            }
            $newId = $this->service->create($dto);
            if ($newId === null) {
                ApiResponse::error('มีรหัสบทบาทนี้อยู่แล้ว', 422);
                return;
            }
            ApiResponse::created($this->service->findById($newId));
        } catch (\Throwable $e) {
            error_log('[RoleController::create] ' . $e->getMessage());
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการบทบาท');
            return;
        }
        try {
            $dto = UpdateRoleDto::fromRequest();
            $errors = $dto->validate();
            if ($errors !== []) {
                ApiResponse::validationFailed($errors);
                return;
            }
            $result = $this->service->update((int) $id, $dto);
            if (!$result['ok']) {
                $this->mapError($result['error'] ?? '');
                return;
            }
            ApiResponse::ok($this->service->findById((int) $id));
        } catch (\Throwable $e) {
            error_log('[RoleController::update] ' . $e->getMessage());
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'role.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการบทบาท');
            return;
        }
        $result = $this->service->delete((int) $id);
        if (!$result['ok']) {
            $this->mapError($result['error'] ?? '');
            return;
        }
        ApiResponse::noContent();
    }

    private function mapError(string $code): void
    {
        switch ($code) {
            case 'not_found':
                ApiResponse::notFound('ไม่พบบทบาท');
                return;
            case 'cannot_disable_system_role':
                ApiResponse::error('ไม่สามารถปิดบทบาทระบบได้', 422);
                return;
            case 'cannot_modify_system_role_permissions':
                ApiResponse::error('ไม่สามารถแก้สิทธิ์ของบทบาทระบบได้', 422);
                return;
            case 'cannot_delete_system_role':
                ApiResponse::error('ไม่สามารถลบบทบาทระบบได้', 422);
                return;
            default:
                ApiResponse::error('ดำเนินการไม่สำเร็จ', 422);
        }
    }
}

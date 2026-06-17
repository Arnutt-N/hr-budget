<?php
declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\AssignGrantDto;
use App\Services\AccessGrantService;
use App\Services\AccessScopeResolver;

/**
 * Manage a user's role-scope grants: /api/v1/users/{userId}/access-grants
 * and DELETE /api/v1/access-grants/{id}.
 */
final class AccessGrantController
{
    public function __construct(
        private readonly AccessGrantService $service = new AccessGrantService(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
    ) {}

    public function listForUser(string $userId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'user.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการผู้ใช้');
            return;
        }
        ApiResponse::ok($this->service->listForUser((int) $userId));
    }

    public function create(string $userId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'user.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการผู้ใช้');
            return;
        }
        try {
            $dto = AssignGrantDto::fromRequest();
            $errors = $dto->validate();
            if ($errors !== []) {
                ApiResponse::validationFailed($errors);
                return;
            }
            $result = $this->service->assign($user, (int) $userId, $dto);
            if (!$result['ok']) {
                $this->mapError($result['error'] ?? '');
                return;
            }
            ApiResponse::created(['id' => $result['id']]);
        } catch (\Throwable $e) {
            error_log('[AccessGrantController::create] ' . $e->getMessage());
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'user.manage')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์จัดการผู้ใช้');
            return;
        }
        $result = $this->service->revoke($user, (int) $id);
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
                ApiResponse::notFound('ไม่พบการมอบสิทธิ์');
                return;
            case 'role_not_found':
                ApiResponse::error('ไม่พบบทบาทที่ระบุ', 422);
                return;
            case 'role_inactive':
                ApiResponse::error('บทบาทนี้ถูกปิดใช้งานอยู่', 422);
                return;
            case 'duplicate_grant':
                ApiResponse::error('มีการมอบสิทธิ์นี้อยู่แล้ว', 422);
                return;
            case 'forbidden_privileged_role':
            case 'forbidden_all_scope':
            case 'forbidden_out_of_scope':
            case 'forbidden_scope_type':
                ApiResponse::forbidden('ไม่มีสิทธิ์มอบบทบาท/ขอบเขตนี้');
                return;
            default:
                ApiResponse::error('ดำเนินการไม่สำเร็จ', 422);
        }
    }
}

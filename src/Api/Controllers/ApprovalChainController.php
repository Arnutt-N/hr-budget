<?php
declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\ApprovalActionDto;
use App\Services\AccessScopeResolver;
use App\Services\ApprovalChainService;

/**
 * Multi-step approval chain endpoints (additive to the existing approval flow):
 *   GET  /api/v1/approval-levels
 *   GET  /api/v1/requests/{id}/approval
 *   POST /api/v1/requests/{id}/approval/approve
 *   POST /api/v1/requests/{id}/approval/reject
 */
final class ApprovalChainController
{
    public function __construct(
        private readonly ApprovalChainService $service = new ApprovalChainService(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
    ) {}

    public function levels(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'request.view')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์ดูสายอนุมัติ');
            return;
        }
        ApiResponse::ok($this->service->levels());
    }

    public function status(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'request.view')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์ดูคำขอ');
            return;
        }
        $status = $this->service->status((int) $id);
        if ($status === null) {
            ApiResponse::notFound('ไม่พบคำขอ');
            return;
        }
        ApiResponse::ok($status);
    }

    public function approve(string $id): void
    {
        $this->act($id, 'approve');
    }

    public function reject(string $id): void
    {
        $this->act($id, 'reject');
    }

    private function act(string $id, string $decision): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        if (!$this->resolver->can($user, 'request.approve')) {
            ApiResponse::forbidden('ไม่มีสิทธิ์อนุมัติ');
            return;
        }
        try {
            $dto = ApprovalActionDto::fromRequest();
            $errors = $dto->validate($decision);
            if ($errors !== []) {
                ApiResponse::validationFailed($errors);
                return;
            }
            $result = $this->service->act($user, (int) $id, $decision, $dto->note);
            if (!$result['ok']) {
                $this->mapError($result['error'] ?? '');
                return;
            }
            ApiResponse::ok([
                'request_status' => $result['status'] ?? null,
                'current_level' => $result['level'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[ApprovalChainController::act] ' . $e->getMessage());
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    private function mapError(string $code): void
    {
        switch ($code) {
            case 'not_found':
                ApiResponse::notFound('ไม่พบคำขอ');
                return;
            case 'not_in_chain':
                ApiResponse::error('คำขอยังไม่เข้าสายอนุมัติ', 422);
                return;
            case 'not_pending':
                ApiResponse::error('คำขอไม่อยู่ในสถานะรออนุมัติ', 422);
                return;
            case 'no_level_config':
                ApiResponse::error('ไม่พบการตั้งค่าระดับอนุมัติ', 422);
                return;
            case 'forbidden_wrong_level_role':
            case 'forbidden_out_of_scope':
                ApiResponse::forbidden('ไม่มีสิทธิ์อนุมัติในระดับ/ขอบเขตนี้');
                return;
            default:
                ApiResponse::error('ดำเนินการไม่สำเร็จ', 422);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreatePositionAllowanceDto;
use App\Dtos\UpdatePositionAllowanceDto;
use App\Services\PositionAllowanceService;

final class PositionAllowanceController
{
    public function __construct(
        private readonly PositionAllowanceService $service = new PositionAllowanceService()
    ) {}

    public function listByPosition(string $positionId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $rows = $this->service->listByPosition((int) $positionId);
        if ($rows === null) {
            ApiResponse::notFound('ไม่พบอัตรากำลัง');
            return;
        }

        ApiResponse::ok($rows);
    }

    public function create(string $positionId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $raw = json_decode((string) file_get_contents('php://input'), true);
            $raw = is_array($raw) ? $raw : [];
            $raw['position_id'] = (int) $positionId;

            $dto = CreatePositionAllowanceDto::fromArray($raw);
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถเพิ่มสิทธิ์ได้ (อัตราหรือชนิดเงินเพิ่มไม่มีจริง)', 422);
                return;
            }

            ApiResponse::created($this->service->listByPosition((int) $positionId));
        } catch (\Throwable $e) {
            error_log("[PositionAllowanceController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function update(string $positionId, string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdatePositionAllowanceDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขสิทธิ์ได้', 422);
                return;
            }

            ApiResponse::ok($this->service->listByPosition((int) $positionId));
        } catch (\Throwable $e) {
            error_log("[PositionAllowanceController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $positionId, string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->delete($user['role'] ?? 'viewer', (int) $id);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบสิทธิ์ได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[PositionAllowanceController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateAllowanceRateDto;
use App\Dtos\UpdateAllowanceRateDto;
use App\Dtos\UpdateAllowanceTypeDto;
use App\Services\AllowanceRateService;
use App\Services\AllowanceTypeService;

final class AllowanceTypeController
{
    public function __construct(
        private readonly AllowanceTypeService $service = new AllowanceTypeService(),
        private readonly AllowanceRateService $rateService = new AllowanceRateService(),
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        ApiResponse::ok($this->service->list());
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $item = $this->service->findById((int) $id);
        if ($item === null) {
            ApiResponse::notFound('ไม่พบชนิดเงินเพิ่ม');
            return;
        }

        ApiResponse::ok($item);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdateAllowanceTypeDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขชนิดเงินเพิ่มได้', 422);
                return;
            }

            ApiResponse::ok($this->service->findById((int) $id));
        } catch (\Throwable $e) {
            error_log("[AllowanceTypeController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function listRates(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $rates = $this->rateService->listByType((int) $id);
        if ($rates === null) {
            ApiResponse::notFound('ไม่พบชนิดเงินเพิ่ม');
            return;
        }

        ApiResponse::ok($rates);
    }

    public function createRate(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $raw = json_decode((string) file_get_contents('php://input'), true);
            $raw = is_array($raw) ? $raw : [];
            $raw['allowance_type_id'] = (int) $id; // ผูกกับ URL เสมอ

            $dto = CreateAllowanceRateDto::fromArray($raw);
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $rateId = $this->rateService->create($user['role'] ?? 'viewer', $dto);
            if ($rateId === null) {
                ApiResponse::error('ไม่สามารถบันทึกอัตราได้ (type ไม่มีจริง หรือเกิดวงจร derived)', 422);
                return;
            }

            ApiResponse::created($this->rateService->listByType((int) $id));
        } catch (\Throwable $e) {
            error_log("[AllowanceTypeController::createRate] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function updateRate(string $id, string $rateId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdateAllowanceRateDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->rateService->update($user['role'] ?? 'viewer', (int) $rateId, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขอัตราได้ (ไม่พบ หรือเกิดวงจร derived)', 422);
                return;
            }

            ApiResponse::ok($this->rateService->listByType((int) $id));
        } catch (\Throwable $e) {
            error_log("[AllowanceTypeController::updateRate] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function deleteRate(string $id, string $rateId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->rateService->delete($user['role'] ?? 'viewer', (int) $rateId);
            if (!$ok) {
                ApiResponse::error(
                    'ไม่สามารถลบอัตราได้ — เป็นอัตราเดียวของชนิดที่มีเงินเพิ่มตัวอื่นอ้างอิง (derived) อยู่ กรุณาปิดช่วงเวลาแทนการลบ หรือตรวจสอบเงินเพิ่มที่อ้างอิงก่อน',
                    422,
                );
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[AllowanceTypeController::deleteRate] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

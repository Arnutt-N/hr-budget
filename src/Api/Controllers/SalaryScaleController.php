<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateSalaryScaleDto;
use App\Dtos\UpdateSalaryScaleDto;
use App\Services\SalaryScaleService;

final class SalaryScaleController
{
    public function __construct(
        private readonly SalaryScaleService $service = new SalaryScaleService()
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

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateSalaryScaleDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างอัตราเงินเดือนได้ (ช่วงเวลาซ้อนกับช่วงที่ยังเปิดอยู่)', 422);
                return;
            }

            ApiResponse::created($this->service->findById($id));
        } catch (\Throwable $e) {
            error_log("[SalaryScaleController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdateSalaryScaleDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขอัตราเงินเดือนได้', 422);
                return;
            }

            ApiResponse::ok($this->service->findById((int) $id));
        } catch (\Throwable $e) {
            error_log("[SalaryScaleController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->delete($user['role'] ?? 'viewer', (int) $id);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบอัตราเงินเดือนได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[SalaryScaleController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

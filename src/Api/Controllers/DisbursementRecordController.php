<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateDisbursementRecordDto;
use App\Dtos\SaveTrackingItemsDto;
use App\Services\DisbursementService;

final class DisbursementRecordController
{
    public function __construct(
        private readonly DisbursementService $service = new DisbursementService()
    ) {}

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateDisbursementRecordDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $record = $this->service->createOrFetchRecord($user['role'] ?? 'viewer', $user, $dto);
            if ($record === null) {
                ApiResponse::error('ไม่สามารถสร้างรายการบันทึกได้ ตรวจสอบรอบบันทึกหรือกิจกรรม', 422);
                return;
            }

            ApiResponse::created($record);
        } catch (\Throwable $e) {
            error_log("[DisbursementRecordController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $detail = $this->service->getRecordDetail($user['role'] ?? 'viewer', $user, (int) $id);
        if ($detail === null) {
            ApiResponse::notFound('ไม่พบรายการบันทึกการเบิกจ่าย');
            return;
        }

        ApiResponse::ok($detail);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = SaveTrackingItemsDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $detail = $this->service->saveRecordItems($user['role'] ?? 'viewer', $user, (int) $id, $dto);
            if ($detail === null) {
                ApiResponse::error('ไม่สามารถบันทึกยอดได้ ตรวจสอบรายการหรือสถานะ', 422);
                return;
            }

            ApiResponse::ok($detail);
        } catch (\Throwable $e) {
            error_log("[DisbursementRecordController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function expenseStructure(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        ApiResponse::ok($this->service->expenseStructure());
    }
}

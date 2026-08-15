<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreatePersonnelBudgetPolicyDto;
use App\Dtos\UpdatePersonnelBudgetPolicyDto;
use App\Services\PersonnelBudgetPolicyService;

final class PersonnelBudgetPolicyController
{
    public function __construct(
        private readonly PersonnelBudgetPolicyService $service = new PersonnelBudgetPolicyService()
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
            $dto = CreatePersonnelBudgetPolicyDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างนโยบายได้ (ปีงบไม่มีจริง หรือมีนโยบายของปีนี้อยู่แล้ว)', 422);
                return;
            }

            ApiResponse::created($this->service->findById($id));
        } catch (\Throwable $e) {
            error_log("[PersonnelBudgetPolicyController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdatePersonnelBudgetPolicyDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขนโยบายได้', 422);
                return;
            }

            ApiResponse::ok($this->service->findById((int) $id));
        } catch (\Throwable $e) {
            error_log("[PersonnelBudgetPolicyController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

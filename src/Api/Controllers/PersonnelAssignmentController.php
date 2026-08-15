<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreatePersonnelAssignmentDto;
use App\Dtos\UpdatePersonnelAssignmentDto;
use App\Services\PersonnelAssignmentService;

final class PersonnelAssignmentController
{
    public function __construct(
        private readonly PersonnelAssignmentService $service = new PersonnelAssignmentService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 50)));

        $filters = array_filter([
            'person_id' => $_GET['person_id'] ?? null,
            'position_id' => $_GET['position_id'] ?? null,
            'serving_organization_id' => $_GET['serving_organization_id'] ?? null,
        ]);

        $result = $this->service->list($page, $perPage, $filters);
        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreatePersonnelAssignmentDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถบันทึกการไปช่วยราชการได้ (อัตราหรือหน่วยงานไม่มีจริง)', 422);
                return;
            }

            ApiResponse::created($this->service->list(1, 50, []));
        } catch (\Throwable $e) {
            error_log("[PersonnelAssignmentController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdatePersonnelAssignmentDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขการไปช่วยราชการได้', 422);
                return;
            }

            ApiResponse::ok($this->service->list(1, 50, []));
        } catch (\Throwable $e) {
            error_log("[PersonnelAssignmentController::update] {$e->getMessage()}");
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
                ApiResponse::error('ไม่สามารถลบการไปช่วยราชการได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[PersonnelAssignmentController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

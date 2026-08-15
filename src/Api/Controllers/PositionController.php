<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreatePositionDto;
use App\Dtos\CreatePositionVersionDto;
use App\Dtos\UpdatePositionDto;
use App\Dtos\UpdatePositionVersionDto;
use App\Services\PositionService;

final class PositionController
{
    public function __construct(
        private readonly PositionService $service = new PositionService()
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
            'organization_id' => $_GET['organization_id'] ?? null,
            'employee_category' => $_GET['employee_category'] ?? null,
            'occupancy' => $_GET['occupancy'] ?? null,
            'approval_status' => $_GET['approval_status'] ?? null,
            'q' => trim((string) ($_GET['q'] ?? '')) !== '' ? trim((string) $_GET['q']) : null,
        ]);

        $result = $this->service->list($page, $perPage, $filters);
        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreatePositionDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างอัตรากำลังได้ อาจมีเลขถือจ่ายนี้อยู่แล้ว', 422);
                return;
            }

            ApiResponse::created($this->service->findById($id));
        } catch (\Throwable $e) {
            error_log("[PositionController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
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
            ApiResponse::notFound('ไม่พบอัตรากำลัง');
            return;
        }

        ApiResponse::ok($item);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdatePositionDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขอัตรากำลังได้ อาจมีเลขถือจ่ายซ้ำหรือไม่พบอัตรา', 422);
                return;
            }

            ApiResponse::ok($this->service->findById((int) $id));
        } catch (\Throwable $e) {
            error_log("[PositionController::update] {$e->getMessage()}");
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
                ApiResponse::error('ไม่สามารถลบอัตรากำลังได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[PositionController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function listVersions(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $versions = $this->service->listVersions((int) $id);
        if ($versions === null) {
            ApiResponse::notFound('ไม่พบอัตรากำลัง');
            return;
        }

        ApiResponse::ok($versions);
    }

    public function createVersion(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreatePositionVersionDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $versionId = $this->service->createVersion($user['role'] ?? 'viewer', (int) $id, $dto);
            if ($versionId === null) {
                ApiResponse::error('ไม่สามารถเพิ่มเวอร์ชันได้', 422);
                return;
            }

            $versions = $this->service->listVersions((int) $id);
            ApiResponse::created($versions);
        } catch (\Throwable $e) {
            error_log("[PositionController::createVersion] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function updateVersion(string $id, string $versionId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdatePositionVersionDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->updateVersion($user['role'] ?? 'viewer', (int) $id, (int) $versionId, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขเวอร์ชันได้ (ช่วงเวลาอาจซ้อนกับเวอร์ชันอื่น)', 422);
                return;
            }

            $versions = $this->service->listVersions((int) $id);
            ApiResponse::ok($versions);
        } catch (\Throwable $e) {
            error_log("[PositionController::updateVersion] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

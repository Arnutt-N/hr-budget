<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateDisbursementSessionDto;
use App\Dtos\DisbursementSessionListQueryDto;
use App\Services\DisbursementService;

final class DisbursementSessionController
{
    public function __construct(
        private readonly DisbursementService $service = new DisbursementService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $query = DisbursementSessionListQueryDto::fromQueryString();
        $errors = $query->validate();
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $result = $this->service->listSessions($user['role'] ?? 'viewer', $user, $query);
        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateDisbursementSessionDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $session = $this->service->createOrFetchSession($user['role'] ?? 'viewer', $user, $dto);
            if ($session === null) {
                ApiResponse::error('ไม่สามารถสร้างรอบบันทึกได้ ตรวจสอบสิทธิ์หรือหน่วยงาน', 422);
                return;
            }

            ApiResponse::created($session);
        } catch (\Throwable $e) {
            error_log("[DisbursementSessionController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $session = $this->service->getSession($user['role'] ?? 'viewer', $user, (int) $id);
        if ($session === null) {
            ApiResponse::notFound('ไม่พบรอบบันทึกการเบิกจ่าย');
            return;
        }

        ApiResponse::ok($session);
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->deleteSession($user['role'] ?? 'viewer', $user, (int) $id);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบได้ ตรวจสอบสิทธิ์หรือรอบบันทึก', 422);
                return;
            }

            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[DisbursementSessionController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function activities(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $activities = $this->service->getActivities($user['role'] ?? 'viewer', $user, (int) $id);
        if ($activities === null) {
            ApiResponse::notFound('ไม่พบรอบบันทึกการเบิกจ่าย');
            return;
        }

        ApiResponse::ok($activities);
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateSalaryRaiseRoundDto;
use App\Services\SalaryRaiseService;

final class SalaryRaiseController
{
    public function __construct(
        private readonly SalaryRaiseService $service = new SalaryRaiseService()
    ) {}

    public function listRounds(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        ApiResponse::ok($this->service->listRounds());
    }

    public function createRound(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateSalaryRaiseRoundDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างรอบเลื่อนได้ (อาจมีรอบเดือน/ปีนี้อยู่แล้ว)', 422);
                return;
            }

            ApiResponse::created($this->service->listRounds());
        } catch (\Throwable $e) {
            error_log("[SalaryRaiseController::createRound] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function setIncludeInBudget(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $raw = json_decode((string) file_get_contents('php://input'), true);
            $include = is_array($raw) && array_key_exists('include_in_budget', $raw)
                ? (bool) $raw['include_in_budget']
                : null;

            if ($include === null) {
                ApiResponse::validationFailed(['include_in_budget' => 'กรุณาระบุสถานะสวิตช์']);
                return;
            }

            $ok = $this->service->setIncludeInBudget($user['role'] ?? 'viewer', (int) $id, $include);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถอัปเดตสวิตช์ได้', 422);
                return;
            }

            ApiResponse::ok($this->service->listRounds());
        } catch (\Throwable $e) {
            error_log("[SalaryRaiseController::setIncludeInBudget] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function listProgress(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $rows = $this->service->listProgress((int) $id);
        if ($rows === null) {
            ApiResponse::notFound('ไม่พบรอบเลื่อน');
            return;
        }

        ApiResponse::ok($rows);
    }

    public function markProgress(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $raw = json_decode((string) file_get_contents('php://input'), true);
            $organizationId = (int) (is_array($raw) ? ($raw['organization_id'] ?? 0) : 0);
            $status = (string) (is_array($raw) ? ($raw['status'] ?? '') : '');
            $docNo = is_array($raw) && isset($raw['doc_no']) ? trim((string) $raw['doc_no']) : null;

            if ($organizationId <= 0) {
                ApiResponse::validationFailed(['organization_id' => 'กรุณาระบุหน่วยงาน']);
                return;
            }

            $ok = $this->service->markProgress($user['role'] ?? 'viewer', (int) $id, $organizationId, $status, $docNo);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถบันทึกสถานะได้ (รอบ/หน่วยงานไม่มีจริง หรือสถานะไม่ถูกต้อง)', 422);
                return;
            }

            ApiResponse::ok($this->service->listProgress((int) $id));
        } catch (\Throwable $e) {
            error_log("[SalaryRaiseController::markProgress] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function seedProgress(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $created = $this->service->seedProgressForAllOrganizations($user['role'] ?? 'viewer', (int) $id);
            if ($created === null) {
                ApiResponse::error('ไม่สามารถสร้างแถวติดตามได้', 422);
                return;
            }

            ApiResponse::ok([
                'created' => $created,
                'progress' => $this->service->listProgress((int) $id),
            ]);
        } catch (\Throwable $e) {
            error_log("[SalaryRaiseController::seedProgress] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

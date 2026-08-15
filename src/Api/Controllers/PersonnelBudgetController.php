<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Services\PersonnelBudgetService;

final class PersonnelBudgetController
{
    public function __construct(
        private readonly PersonnelBudgetService $service = new PersonnelBudgetService()
    ) {}

    /** POST /api/v1/personnel-budget/compute { fiscal_year_id, dry_run } */
    public function compute(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        try {
            $raw = json_decode((string) file_get_contents('php://input'), true);
            $raw = is_array($raw) ? $raw : [];

            $fiscalYearId = (int) ($raw['fiscal_year_id'] ?? 0);
            if ($fiscalYearId <= 0) {
                ApiResponse::validationFailed(['fiscal_year_id' => 'กรุณาระบุปีงบประมาณ']);
                return;
            }

            $dryRun = (bool) ($raw['dry_run'] ?? false);
            $result = $this->service->compute($fiscalYearId, dryRun: $dryRun);

            if ($result['lines'] === []) {
                ApiResponse::ok([
                    'message' => 'ไม่พบอัตรากำลังที่นับได้สำหรับปีงบนี้ (ตรวจสอบ policy/เกณฑ์อัตราว่าง/สถานะอนุมัติ)',
                    'lines' => [],
                ]);
                return;
            }

            ApiResponse::ok($result + ['dry_run' => $dryRun]);
        } catch (\Throwable $e) {
            error_log("[PersonnelBudgetController::compute] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

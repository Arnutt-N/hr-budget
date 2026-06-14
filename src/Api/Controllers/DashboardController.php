<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Services\DashboardService;

/**
 * Read-only dashboard API. Any authenticated user may read (no admin gate) —
 * mirrors the legacy web dashboard which only required a logged-in session.
 */
final class DashboardController
{
    public function __construct(
        private readonly DashboardService $service = new DashboardService()
    ) {}

    public function summary(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $fiscalYear = $this->resolveFiscalYear();
            ApiResponse::ok($this->service->summary($fiscalYear));
        } catch (\Throwable $e) {
            error_log("[DashboardController::summary] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function chartData(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $fiscalYear = $this->resolveFiscalYear();
            ApiResponse::ok($this->service->monthlyExpenditure($fiscalYear));
        } catch (\Throwable $e) {
            error_log("[DashboardController::chartData] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    /**
     * Fiscal year (Buddhist era) from ?year= query param, else config default.
     */
    private function resolveFiscalYear(): int
    {
        $param = $_GET['year'] ?? null;
        if ($param !== null && ctype_digit((string) $param)) {
            return (int) $param;
        }

        $config = require __DIR__ . '/../../../config/app.php';
        return (int) ($config['fiscal_year']['current'] ?? 2569);
    }
}

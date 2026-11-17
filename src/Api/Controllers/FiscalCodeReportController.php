<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Models\FiscalYear;
use App\Services\FiscalCodeReportService;

final class FiscalCodeReportController
{
    public function __construct(
        private readonly FiscalCodeReportService $service = new FiscalCodeReportService(),
    ) {}

    public function byExpenseCategory(): void
    {
        $this->report(fn (): array => $this->service->byExpenseCategory($this->resolveFiscalYear()));
    }

    public function byActivity(): void
    {
        $this->report(fn (): array => $this->service->byActivity($this->resolveFiscalYear()));
    }

    public function byParentRollup(): void
    {
        $this->report(fn (): array => $this->service->byParentRollup());
    }

    public function byAgency(): void
    {
        $this->report(fn (): array => $this->service->byAgency());
    }

    /** @param callable(): list<array<string,mixed>> $rows */
    private function report(callable $rows): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($rows());
        } catch (\Throwable $e) {
            error_log("[FiscalCodeReportController] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    private function resolveFiscalYear(): int
    {
        $param = $_GET['year'] ?? null;
        if ($param !== null && ctype_digit((string) $param)) {
            return (int) $param;
        }

        $current = FiscalYear::current();
        if ($current !== null) {
            return (int) $current['year'];
        }

        $config = require __DIR__ . '/../../../config/app.php';
        return (int) ($config['fiscal_year']['current'] ?? 2569);
    }
}

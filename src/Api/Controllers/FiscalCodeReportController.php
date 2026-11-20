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

    /** Read-only year options for the report page, available to any authenticated user. */
    public function years(bool $exit = true): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($this->buildYearOptions(), [], $exit);
        } catch (\Throwable $e) {
            error_log("[FiscalCodeReportController::years] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500, null, $exit);
        }
    }

    /** @return list<array{year:int,is_current:bool}> */
    private function buildYearOptions(): array
    {
        $rows = FiscalYear::all();
        if ($rows === []) {
            return [['year' => $this->resolveFiscalYear(), 'is_current' => true]];
        }

        $options = [];
        foreach ($rows as $row) {
            $options[] = [
                'year' => (int) $row['year'],
                'is_current' => (bool) $row['is_current'],
            ];
        }

        return $options;
    }

    public function byExpenseCategory(): void
    {
        $this->report(fn (array $user): array => $this->service->byExpenseCategory($user, $this->resolveFiscalYear()));
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

    /** @param callable(array): list<array<string,mixed>> $rows */
    private function report(callable $rows): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            ApiResponse::ok($rows($user));
        } catch (\Throwable $e) {
            error_log("[FiscalCodeReportController] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    private function resolveFiscalYear(): int
    {
        $param = $_GET['year'] ?? null;
        if (is_string($param) && ctype_digit($param)) {
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

<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\AnalyticsQueryDto;
use App\Services\AnalyticsService;

/**
 * Read-only analytics API. Any authenticated user may read; results are
 * org-scoped per the caller's RBAC grants inside AnalyticsService, so there is
 * no separate permission gate here (mirrors DashboardController).
 *
 * Each method: CORS → auth (yields the user array) → validate input DTO
 * (422 on bad fiscal_year / dimension) → delegate to the service, wrapping any
 * failure in a generic Thai 500 without leaking the exception message.
 */
final class AnalyticsController
{
    public function __construct(
        private readonly AnalyticsService $service = new AnalyticsService()
    ) {}

    public function comparison(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = AnalyticsQueryDto::fromQueryString();
        $errors = $dto->validate();
        if ($errors !== []) {
            ApiResponse::validationFailed($errors);
            return;
        }

        try {
            ApiResponse::ok($this->service->comparison($user, $dto->fiscalYear, $dto->dimension));
        } catch (\Throwable $e) {
            error_log("[AnalyticsController::comparison] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function forecast(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = AnalyticsQueryDto::fromQueryString();
        $errors = $dto->validate();
        if ($errors !== []) {
            ApiResponse::validationFailed($errors);
            return;
        }

        try {
            ApiResponse::ok($this->service->forecast($user, $dto->fiscalYear));
        } catch (\Throwable $e) {
            error_log("[AnalyticsController::forecast] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function requestVsApproved(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = AnalyticsQueryDto::fromQueryString();
        $errors = $dto->validate();
        if ($errors !== []) {
            ApiResponse::validationFailed($errors);
            return;
        }

        try {
            ApiResponse::ok($this->service->requestVsApproved($user, $dto->fiscalYear));
        } catch (\Throwable $e) {
            error_log("[AnalyticsController::requestVsApproved] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

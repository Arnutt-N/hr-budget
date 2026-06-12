<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\NotificationQueryDto;
use App\Services\NotificationService;

final class NotificationController
{
    public function __construct(
        private readonly NotificationService $service = new NotificationService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = NotificationQueryDto::fromQueryString();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $result = $this->service->list((int) $user['id'], $dto);
            ApiResponse::ok($result['data'], $result['meta']);
        } catch (\Throwable $e) {
            error_log("[NotificationController::list] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function unreadCount(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $count = $this->service->getUnreadCount((int) $user['id']);
            ApiResponse::ok(['unread_count' => $count]);
        } catch (\Throwable $e) {
            error_log("[NotificationController::unreadCount] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function markRead(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->markRead((int) $id, (int) $user['id']);
            if (!$ok) {
                ApiResponse::error('ไม่พบการแจ้งเตือน', 404);
                return;
            }
            ApiResponse::ok(['marked_read' => true]);
        } catch (\Throwable $e) {
            error_log("[NotificationController::markRead] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function markAllRead(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $this->service->markAllRead((int) $user['id']);
            ApiResponse::ok(['marked_all_read' => true]);
        } catch (\Throwable $e) {
            error_log("[NotificationController::markAllRead] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

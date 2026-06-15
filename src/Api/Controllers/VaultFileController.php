<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Core\Download;
use App\Services\VaultService;

final class VaultFileController
{
    public function __construct(
        private readonly VaultService $service = new VaultService()
    ) {}

    public function download(string $id): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $info = $this->service->getDownloadInfo((int) $id);
            if ($info === null) {
                ApiResponse::notFound('ไม่พบไฟล์');
                return;
            }

            // Hardened streaming: CRLF/MIME guard, nosniff, RFC 5987 filename*.
            Download::sendFile($info['path'], $info['name'], $info['mime']);
        } catch (\Throwable $e) {
            error_log("[VaultFileController::download] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $result = $this->service->deleteFile((int) $id, $user['role'] ?? 'viewer');
            if (!$result['success']) {
                ApiResponse::error($result['error'], $result['status'] ?? 422);
                return;
            }

            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[VaultFileController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

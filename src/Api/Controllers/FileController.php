<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateFileDto;
use App\Services\FileService;

final class FileController
{
    public function __construct(
        private readonly FileService $service = new FileService()
    ) {}

    public function upload(string $requestId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateFileDto::fromUpload();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $result = $this->service->upload((int) $requestId, $dto, (int) $user['id'], $user['role'] ?? 'viewer');
            if (!$result['success']) {
                ApiResponse::error($result['error'], 422);
                return;
            }

            ApiResponse::created($result['file']);
        } catch (\Throwable $e) {
            error_log("[FileController::upload] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function list(string $requestId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $files = $this->service->listByRequest((int) $requestId, (int) $user['id'], $user['role'] ?? 'viewer');
            ApiResponse::ok($files);
        } catch (\Throwable $e) {
            error_log("[FileController::list] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function download(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $info = $this->service->getDownloadInfo((int) $id, (int) $user['id'], $user['role'] ?? 'viewer');
            if ($info === null) {
                ApiResponse::notFound('ไม่พบไฟล์');
                return;
            }

            header('Content-Type: ' . $info['mime']);
            $safeName = str_replace(["\r", "\n", '"'], '', basename($info['name']));
            header('Content-Disposition: attachment; filename="' . $safeName . '"');
            header('Content-Length: ' . filesize($info['path']));
            readfile($info['path']);
            exit;
        } catch (\Throwable $e) {
            error_log("[FileController::download] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->delete((int) $id, (int) $user['id'], $user['role'] ?? 'viewer');
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบไฟล์ได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[FileController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

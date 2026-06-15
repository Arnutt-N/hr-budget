<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateFileDto;
use App\Dtos\CreateFolderDto;
use App\Services\VaultService;

final class VaultFolderController
{
    public function __construct(
        private readonly VaultService $service = new VaultService()
    ) {}

    public function listFolders(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $year = (int) ($_GET['year'] ?? 0);
            $parent = isset($_GET['parent']) && $_GET['parent'] !== '' ? (int) $_GET['parent'] : null;

            // A root listing needs a fiscal year; a child listing is keyed by parent.
            if ($parent === null && $year <= 0) {
                ApiResponse::error('กรุณาระบุปีงบประมาณ', 400);
                return;
            }

            ApiResponse::ok([
                'folders' => $this->service->listFolders($year, $parent),
                'breadcrumb' => $this->service->breadcrumb($parent),
            ]);
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::listFolders] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function tree(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $year = (int) ($_GET['year'] ?? 0);
            if ($year <= 0) {
                ApiResponse::error('กรุณาระบุปีงบประมาณ', 400);
                return;
            }

            ApiResponse::ok($this->service->tree($year));
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::tree] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function years(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($this->service->years());
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::years] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateFolderDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $result = $this->service->createFolder($dto, (int) $user['id'], $user['role'] ?? 'viewer');
            if (!$result['success']) {
                ApiResponse::error($result['error'], $result['status'] ?? 422);
                return;
            }

            ApiResponse::created($result['folder']);
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $result = $this->service->deleteFolder((int) $id, $user['role'] ?? 'viewer');
            if (!$result['success']) {
                ApiResponse::error($result['error'], $result['status'] ?? 422);
                return;
            }

            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function listFiles(string $folderId): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($this->service->listFiles((int) $folderId));
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::listFiles] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function upload(string $folderId): void
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

            $result = $this->service->upload((int) $folderId, $dto, (int) $user['id'], $user['role'] ?? 'viewer');
            if (!$result['success']) {
                ApiResponse::error($result['error'], $result['status'] ?? 422);
                return;
            }

            ApiResponse::created($result['file']);
        } catch (\Throwable $e) {
            error_log("[VaultFolderController::upload] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

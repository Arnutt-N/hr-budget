<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateUserDto;
use App\Dtos\UpdateUserDto;
use App\Services\UserService;

final class UserController
{
    public function __construct(
        private readonly UserService $service = new UserService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 50)));
        $result = $this->service->list($page, $perPage);

        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์');
            return;
        }

        try {
            $dto = CreateUserDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'], $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างผู้ใช้ได้ อาจมีอีเมลนี้อยู่แล้ว', 422);
                return;
            }

            $item = $this->service->findById($id);
            ApiResponse::created($item);
        } catch (\Throwable $e) {
            error_log("[UserController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์');
            return;
        }

        $item = $this->service->findById((int) $id);
        if ($item === null) {
            ApiResponse::notFound('ไม่พบผู้ใช้');
            return;
        }

        ApiResponse::ok($item);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์');
            return;
        }

        try {
            $dto = UpdateUserDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'], (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขผู้ใช้ได้ อาจมีอีเมลซ้ำ', 422);
                return;
            }

            $item = $this->service->findById((int) $id);
            ApiResponse::ok($item);
        } catch (\Throwable $e) {
            error_log("[UserController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์');
            return;
        }

        try {
            $ok = $this->service->delete($user['role'], (int) $user['id'], (int) $id);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบผู้ใช้ได้ ไม่สามารถลบตัวเองได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[UserController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

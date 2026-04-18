<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\CreateCategoryDto;
use App\Dtos\CreateCategoryItemDto;
use App\Dtos\UpdateCategoryDto;
use App\Dtos\UpdateCategoryItemDto;
use App\Services\BudgetCategoryService;

final class BudgetCategoryController
{
    public function __construct(
        private readonly BudgetCategoryService $service = new BudgetCategoryService()
    ) {}

    // --- Categories ---

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 100)));
        $result = $this->service->list($page, $perPage);

        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function tree(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $data = $this->service->getTree();
        ApiResponse::ok($data);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateCategoryDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->create($user['role'] ?? 'viewer', $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างหมวดงบประมาณได้', 422);
                return;
            }

            $item = $this->service->findById($id);
            ApiResponse::created($item);
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::create] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $item = $this->service->findById((int) $id);
        if ($item === null) {
            ApiResponse::notFound('ไม่พบหมวดงบประมาณ');
            return;
        }

        ApiResponse::ok($item);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdateCategoryDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->update($user['role'] ?? 'viewer', (int) $id, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขหมวดงบประมาณได้', 422);
                return;
            }

            $item = $this->service->findById((int) $id);
            ApiResponse::ok($item);
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::update] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->delete($user['role'] ?? 'viewer', (int) $id);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบหมวดงบประมาณได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::delete] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    // --- Category Items ---

    public function listItems(string $categoryId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        if (($user['role'] ?? '') !== 'admin') {
            ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
            return;
        }

        $items = $this->service->listItems((int) $categoryId);
        ApiResponse::ok($items);
    }

    public function createItem(string $categoryId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = CreateCategoryItemDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $id = $this->service->createItem($user['role'] ?? 'viewer', (int) $categoryId, $dto);
            if ($id === null) {
                ApiResponse::error('ไม่สามารถสร้างรายการได้', 422);
                return;
            }

            $item = $this->service->listItems((int) $categoryId);
            ApiResponse::created($item);
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::createItem] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function updateItem(string $categoryId, string $itemId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $dto = UpdateCategoryItemDto::fromRequest();
            $errors = $dto->validate();
            if (!empty($errors)) {
                ApiResponse::validationFailed($errors);
                return;
            }

            $ok = $this->service->updateItem($user['role'] ?? 'viewer', (int) $itemId, $dto);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถแก้ไขรายการได้', 422);
                return;
            }

            $items = $this->service->listItems((int) $categoryId);
            ApiResponse::ok($items);
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::updateItem] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function deleteItem(string $categoryId, string $itemId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->deleteItem($user['role'] ?? 'viewer', (int) $itemId);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถลบรายการได้', 422);
                return;
            }
            ApiResponse::noContent();
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::deleteItem] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function restoreItem(string $categoryId, string $itemId): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        try {
            $ok = $this->service->restoreItem($user['role'] ?? 'viewer', (int) $itemId);
            if (!$ok) {
                ApiResponse::error('ไม่สามารถกู้คืนรายการได้', 422);
                return;
            }

            $items = $this->service->listItems((int) $categoryId);
            ApiResponse::ok($items);
        } catch (\Throwable $e) {
            error_log("[BudgetCategoryController::restoreItem] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}

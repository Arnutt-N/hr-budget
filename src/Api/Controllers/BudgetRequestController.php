<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Dtos\ApprovalActionDto;
use App\Dtos\BudgetRequestListQueryDto;
use App\Dtos\CreateBudgetRequestDto;
use App\Dtos\UpdateBudgetRequestDto;
use App\Services\BudgetRequestService;

final class BudgetRequestController
{
    public function __construct(
        private readonly BudgetRequestService $service = new BudgetRequestService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $query = BudgetRequestListQueryDto::fromQueryString();
        $errors = $query->validate();
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $result = $this->service->list(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            $query
        );

        ApiResponse::ok($result['data'], $result['meta']);
    }

    public function create(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = CreateBudgetRequestDto::fromRequest();
        $errors = $dto->validate();
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $id = $this->service->create((int) $user['id'], $dto);
        if ($id === null) {
            ApiResponse::error('ไม่สามารถสร้างคำขอได้', 500);
            return;
        }

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            $id
        );

        ApiResponse::created($request);
    }

    public function show(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        if ($request === null) {
            ApiResponse::notFound('ไม่พบคำของบประมาณ');
            return;
        }

        ApiResponse::ok($request);
    }

    public function update(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = UpdateBudgetRequestDto::fromRequest();
        $errors = $dto->validate();
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $ok = $this->service->update(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id,
            $dto
        );

        if (!$ok) {
            ApiResponse::error('ไม่สามารถแก้ไขคำขอได้ อาจเป็นเพราะสถานะไม่อนุญาต', 422);
            return;
        }

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        ApiResponse::ok($request);
    }

    public function delete(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $ok = $this->service->delete(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        if (!$ok) {
            ApiResponse::error('ไม่สามารถลบคำขอได้', 422);
            return;
        }

        ApiResponse::noContent();
    }

    public function submit(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $ok = $this->service->submit((int) $user['id'], (int) $id);
        if (!$ok) {
            ApiResponse::error('ไม่สามารถส่งอนุมัติได้ อาจเป็นเพราะสถานะไม่อนุญาต', 422);
            return;
        }

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        ApiResponse::ok($request);
    }

    public function approve(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = ApprovalActionDto::fromRequest();
        $errors = $dto->validate('approve');
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $ok = $this->service->approve((int) $user['id'], (int) $id, $dto);
        if (!$ok) {
            ApiResponse::error('ไม่สามารถอนุมัติได้ อาจเป็นเพราะสถานะไม่ใช่รออนุมัติ', 422);
            return;
        }

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        ApiResponse::ok($request);
    }

    public function reject(string $id): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        $dto = ApprovalActionDto::fromRequest();
        $errors = $dto->validate('reject');
        if (!empty($errors)) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $ok = $this->service->reject((int) $user['id'], (int) $id, $dto);
        if (!$ok) {
            ApiResponse::error('ไม่สามารถปฏิเสธได้ อาจเป็นเพราะสถานะไม่ใช่รออนุมัติ', 422);
            return;
        }

        $request = $this->service->findById(
            (int) $user['id'],
            $user['role'] ?? 'staff',
            (int) $id
        );

        ApiResponse::ok($request);
    }
}

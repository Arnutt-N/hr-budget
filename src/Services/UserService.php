<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateUserDto;
use App\Dtos\UpdateUserDto;
use App\Repositories\UserRepository;

final class UserService
{
    public function __construct(
        private readonly UserRepository $repo = new UserRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->repo->count();
        $data = $this->repo->findAll($perPage, $offset);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ],
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function create(string $role, CreateUserDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->repo->emailExists($dto->email)) {
            return null;
        }

        return $this->repo->insert([
            'email' => $dto->email,
            'password' => password_hash($dto->password, PASSWORD_DEFAULT),
            'name' => $dto->name,
            'role' => $dto->role,
            'is_active' => $dto->isActive ? 1 : 0,
            'department' => $dto->department,
        ]);
    }

    public function update(string $role, int $id, UpdateUserDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        if ($dto->email !== null && $this->repo->emailExists($dto->email, $id)) {
            return false;
        }

        $updateData = [];
        if ($dto->email !== null) $updateData['email'] = $dto->email;
        if ($dto->password !== null) $updateData['password'] = password_hash($dto->password, PASSWORD_DEFAULT);
        if ($dto->name !== null) $updateData['name'] = $dto->name;
        if ($dto->role !== null) $updateData['role'] = $dto->role;
        if ($dto->isActive !== null) $updateData['is_active'] = $dto->isActive ? 1 : 0;
        if ($dto->department !== null) $updateData['department'] = $dto->department;

        if (empty($updateData)) {
            return true;
        }

        return $this->repo->update($id, $updateData);
    }

    public function delete(string $role, int $userId, int $targetId): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if ($userId === $targetId) {
            return false;
        }

        return $this->repo->delete($targetId);
    }
}

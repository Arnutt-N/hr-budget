<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class UserRepository
{
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        return Database::query(
            "SELECT id, email, name, role, is_active, department, created_at, updated_at, last_login_at
             FROM users ORDER BY id LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function count(): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM users");
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        $row = Database::queryOne("SELECT * FROM users WHERE id = ?", [$id]);
        if ($row !== null) {
            unset($row['password']);
        }
        return $row;
    }

    public function findByEmail(string $email): ?array
    {
        return Database::queryOne(
            "SELECT id, email, name, role, is_active, department, created_at, updated_at, last_login_at FROM users WHERE email = ?",
            [$email]
        );
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $result = Database::queryOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $excludeId]);
        } else {
            $result = Database::queryOne("SELECT id FROM users WHERE email = ?", [$email]);
        }
        return $result !== null;
    }

    public function insert(array $data): int
    {
        return Database::insert('users', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['email', 'password', 'name', 'role', 'is_active', 'department'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (empty($updateData)) {
            return false;
        }
        return Database::update('users', $updateData, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('users', 'id = ?', [$id]) > 0;
    }
}

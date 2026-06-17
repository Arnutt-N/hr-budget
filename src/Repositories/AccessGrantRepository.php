<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Data access for user_access_grants (user <-> role <-> scope assignments).
 */
class AccessGrantRepository
{
    /**
     * Active grants for a user joined with role info.
     * @return array<int,array>
     */
    public function findActiveByUser(int $userId): array
    {
        return Database::query(
            "SELECT g.*, r.code AS role_code, r.name_th AS role_name_th, r.is_active AS role_is_active
             FROM user_access_grants g
             JOIN roles r ON r.id = g.role_id
             WHERE g.user_id = ? AND g.is_active = 1 AND r.is_active = 1
             ORDER BY g.id",
            [$userId]
        );
    }

    /** All grants (active or not) for a user — for management UI. */
    public function findByUser(int $userId): array
    {
        return Database::query(
            "SELECT g.*, r.code AS role_code, r.name_th AS role_name_th
             FROM user_access_grants g
             JOIN roles r ON r.id = g.role_id
             WHERE g.user_id = ? ORDER BY g.id",
            [$userId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM user_access_grants WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        return Database::insert('user_access_grants', $data);
    }

    public function delete(int $id): bool
    {
        return Database::delete('user_access_grants', 'id = ?', [$id]) > 0;
    }

    /** Distinct permission codes a user holds across all active roles. */
    public function permissionCodesForUser(int $userId): array
    {
        $rows = Database::query(
            "SELECT DISTINCT p.code
             FROM user_access_grants g
             JOIN roles r ON r.id = g.role_id AND r.is_active = 1
             JOIN role_permissions rp ON rp.role_id = r.id
             JOIN permissions p ON p.id = rp.permission_id
             WHERE g.user_id = ? AND g.is_active = 1",
            [$userId]
        );
        return array_map(static fn ($r) => $r['code'], $rows);
    }

    /** True if the user has an active grant of an active role with the given code. */
    public function userHasActiveRole(int $userId, string $roleCode): bool
    {
        $row = Database::queryOne(
            "SELECT 1 FROM user_access_grants g
             JOIN roles r ON r.id = g.role_id
             WHERE g.user_id = ? AND g.is_active = 1 AND r.is_active = 1 AND r.code = ?
             LIMIT 1",
            [$userId, $roleCode]
        );
        return $row !== null;
    }
}

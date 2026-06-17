<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Data access for RBAC roles + their permission assignments.
 */
class RoleRepository
{
    /** @return array<int,array> */
    public function findAll(bool $includeInactive = true): array
    {
        $sql = "SELECT * FROM roles";
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order, id";
        return Database::query($sql);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM roles WHERE id = ?", [$id]);
    }

    public function findByCode(string $code): ?array
    {
        return Database::queryOne("SELECT * FROM roles WHERE code = ?", [$code]);
    }

    public function insert(array $data): int
    {
        return Database::insert('roles', $data);
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['code', 'name_th', 'name_en', 'description', 'is_active', 'sort_order'];
        $clean = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $clean[$f] = $data[$f];
            }
        }
        if (empty($clean)) {
            return false;
        }
        return Database::update('roles', $clean, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return Database::delete('roles', 'id = ? AND is_system = 0', [$id]) > 0;
    }

    /** @return array<int,string> permission codes granted to the role */
    public function getPermissionCodes(int $roleId): array
    {
        $rows = Database::query(
            "SELECT p.code FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ? ORDER BY p.code",
            [$roleId]
        );
        return array_map(static fn ($r) => $r['code'], $rows);
    }

    /**
     * Replace the role's permission set with the given permission ids (transactional).
     * @param array<int,int> $permissionIds
     */
    public function setPermissions(int $roleId, array $permissionIds): void
    {
        Database::beginTransaction();
        try {
            Database::delete('role_permissions', 'role_id = ?', [$roleId]);
            foreach (array_unique($permissionIds) as $pid) {
                Database::insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => (int) $pid,
                ]);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }
}

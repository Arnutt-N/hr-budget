<?php
declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateRoleDto;
use App\Dtos\UpdateRoleDto;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;

/**
 * Business logic for managing RBAC roles and their permission sets.
 * Authorization (role.manage) is enforced by the controller via AccessScopeResolver.
 */
final class RoleService
{
    public function __construct(
        private readonly RoleRepository $roles = new RoleRepository(),
        private readonly PermissionRepository $permissions = new PermissionRepository(),
    ) {}

    /** @return array<int,array> roles enriched with their permission codes */
    public function list(): array
    {
        $rows = $this->roles->findAll(true);
        foreach ($rows as &$r) {
            $r['permissions'] = $this->roles->getPermissionCodes((int) $r['id']);
        }
        return $rows;
    }

    public function findById(int $id): ?array
    {
        $role = $this->roles->findById($id);
        if ($role === null) {
            return null;
        }
        $role['permissions'] = $this->roles->getPermissionCodes($id);
        return $role;
    }

    /** @return int|null new role id, or null if the code already exists */
    public function create(CreateRoleDto $dto): ?int
    {
        if ($this->roles->findByCode($dto->code) !== null) {
            return null;
        }
        $id = $this->roles->insert([
            'code' => $dto->code,
            'name_th' => $dto->nameTh,
            'name_en' => $dto->nameEn,
            'description' => $dto->description,
            'is_system' => 0,
            'is_active' => 1,
        ]);
        if ($dto->permissions !== null) {
            $this->roles->setPermissions($id, $this->permissions->idsForCodes($dto->permissions));
        }
        return $id;
    }

    /**
     * @return array{ok:bool,error?:string}
     */
    public function update(int $id, UpdateRoleDto $dto): array
    {
        $role = $this->roles->findById($id);
        if ($role === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        $isSystem = (int) $role['is_system'] === 1;

        // System roles (e.g. super_admin) cannot be disabled or have perms rewritten.
        if ($isSystem && $dto->isActive === false) {
            return ['ok' => false, 'error' => 'cannot_disable_system_role'];
        }
        if ($isSystem && $dto->permissions !== null) {
            return ['ok' => false, 'error' => 'cannot_modify_system_role_permissions'];
        }

        $data = [];
        if ($dto->nameTh !== null) {
            $data['name_th'] = $dto->nameTh;
        }
        if ($dto->nameEn !== null) {
            $data['name_en'] = $dto->nameEn;
        }
        if ($dto->description !== null) {
            $data['description'] = $dto->description;
        }
        if ($dto->isActive !== null) {
            $data['is_active'] = $dto->isActive ? 1 : 0;
        }
        if ($dto->sortOrder !== null) {
            $data['sort_order'] = $dto->sortOrder;
        }
        if ($data !== []) {
            $this->roles->update($id, $data);
        }
        if ($dto->permissions !== null) {
            $this->roles->setPermissions($id, $this->permissions->idsForCodes($dto->permissions));
        }
        return ['ok' => true];
    }

    /** @return array{ok:bool,error?:string} */
    public function delete(int $id): array
    {
        $role = $this->roles->findById($id);
        if ($role === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if ((int) $role['is_system'] === 1) {
            return ['ok' => false, 'error' => 'cannot_delete_system_role'];
        }
        return ['ok' => $this->roles->delete($id)];
    }

    /** @return array<int,array> the fixed permission catalogue */
    public function listPermissions(): array
    {
        return $this->permissions->findAll();
    }
}

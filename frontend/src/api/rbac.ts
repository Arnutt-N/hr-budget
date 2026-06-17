import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  Role,
  Permission,
  AccessGrant,
  AssignGrantPayload,
  CreateRolePayload,
  UpdateRolePayload,
  MyPermissions,
} from '@/types/rbac'

// --- Current user effective permissions ---
export async function fetchMyPermissions(): Promise<ApiResponse<MyPermissions>> {
  return apiFetch<MyPermissions>('/me/permissions')
}

// --- Permission catalogue ---
export async function fetchPermissions(): Promise<ApiResponse<Permission[]>> {
  return apiFetch<Permission[]>('/permissions')
}

// --- Roles ---
export async function fetchRoles(): Promise<ApiResponse<Role[]>> {
  return apiFetch<Role[]>('/roles')
}

export async function createRole(data: CreateRolePayload): Promise<ApiResponse<Role>> {
  return apiFetch<Role>('/roles', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateRole(
  id: number,
  data: UpdateRolePayload,
): Promise<ApiResponse<Role>> {
  return apiFetch<Role>(`/roles/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteRole(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/roles/${id}`, { method: 'DELETE' })
}

// --- Per-user access grants ---
export async function fetchUserGrants(userId: number): Promise<ApiResponse<AccessGrant[]>> {
  return apiFetch<AccessGrant[]>(`/users/${userId}/access-grants`)
}

export async function createGrant(
  userId: number,
  data: AssignGrantPayload,
): Promise<ApiResponse<{ id: number }>> {
  return apiFetch<{ id: number }>(`/users/${userId}/access-grants`, {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export async function deleteGrant(grantId: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/access-grants/${grantId}`, { method: 'DELETE' })
}

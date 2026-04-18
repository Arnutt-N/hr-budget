import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { User, CreateUser, UpdateUser } from '@/types/user'

export async function fetchUsers(page = 1, perPage = 50): Promise<ApiResponse<User[]>> {
  return apiFetch<User[]>(`/users?page=${page}&per_page=${perPage}`)
}

export async function fetchUserById(id: number): Promise<ApiResponse<User>> {
  return apiFetch<User>(`/users/${id}`)
}

export async function createUser(data: CreateUser): Promise<ApiResponse<User>> {
  return apiFetch<User>('/users', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateUser(id: number, data: UpdateUser): Promise<ApiResponse<User>> {
  return apiFetch<User>(`/users/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteUser(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/users/${id}`, { method: 'DELETE' })
}

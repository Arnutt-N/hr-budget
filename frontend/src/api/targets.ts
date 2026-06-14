import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { Target, CreateTarget, UpdateTarget } from '@/types/target'

export async function fetchTargets(page = 1, perPage = 100): Promise<ApiResponse<Target[]>> {
  return apiFetch<Target[]>(`/targets?page=${page}&per_page=${perPage}`)
}

export async function fetchTargetById(id: number): Promise<ApiResponse<Target>> {
  return apiFetch<Target>(`/targets/${id}`)
}

export async function createTarget(data: CreateTarget): Promise<ApiResponse<Target>> {
  return apiFetch<Target>('/targets', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateTarget(id: number, data: UpdateTarget): Promise<ApiResponse<Target>> {
  return apiFetch<Target>(`/targets/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteTarget(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/targets/${id}`, { method: 'DELETE' })
}

import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { TargetType, CreateTargetType, UpdateTargetType } from '@/types/target-type'

export async function fetchTargetTypes(page = 1, perPage = 100): Promise<ApiResponse<TargetType[]>> {
  return apiFetch<TargetType[]>(`/target-types?page=${page}&per_page=${perPage}`)
}

export async function fetchTargetTypeById(id: number): Promise<ApiResponse<TargetType>> {
  return apiFetch<TargetType>(`/target-types/${id}`)
}

export async function createTargetType(data: CreateTargetType): Promise<ApiResponse<TargetType>> {
  return apiFetch<TargetType>('/target-types', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateTargetType(id: number, data: UpdateTargetType): Promise<ApiResponse<TargetType>> {
  return apiFetch<TargetType>(`/target-types/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteTargetType(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/target-types/${id}`, { method: 'DELETE' })
}

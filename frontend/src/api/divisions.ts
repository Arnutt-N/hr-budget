import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { Division, CreateDivision, UpdateDivision } from '@/types/division'

export async function fetchDivisions(page = 1, perPage = 100): Promise<ApiResponse<Division[]>> {
  return apiFetch<Division[]>(`/divisions?page=${page}&per_page=${perPage}`)
}

export async function fetchDivisionById(id: number): Promise<ApiResponse<Division>> {
  return apiFetch<Division>(`/divisions/${id}`)
}

export async function createDivision(data: CreateDivision): Promise<ApiResponse<Division>> {
  return apiFetch<Division>('/divisions', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateDivision(id: number, data: UpdateDivision): Promise<ApiResponse<Division>> {
  return apiFetch<Division>(`/divisions/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteDivision(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/divisions/${id}`, { method: 'DELETE' })
}

import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { AllowanceType, UpdateAllowanceType, AllowanceRate, CreateAllowanceRate } from '@/types/allowance'

export async function fetchAllowanceTypes(): Promise<ApiResponse<AllowanceType[]>> {
  return apiFetch<AllowanceType[]>('/allowance-types')
}

export async function updateAllowanceType(
  id: number,
  data: UpdateAllowanceType,
): Promise<ApiResponse<AllowanceType>> {
  return apiFetch<AllowanceType>(`/allowance-types/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function fetchAllowanceRates(typeId: number): Promise<ApiResponse<AllowanceRate[]>> {
  return apiFetch<AllowanceRate[]>(`/allowance-types/${typeId}/rates`)
}

export async function createAllowanceRate(
  typeId: number,
  data: CreateAllowanceRate,
): Promise<ApiResponse<AllowanceRate[]>> {
  return apiFetch<AllowanceRate[]>(`/allowance-types/${typeId}/rates`, { method: 'POST', body: JSON.stringify(data) })
}

export async function deleteAllowanceRate(typeId: number, rateId: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/allowance-types/${typeId}/rates/${rateId}`, { method: 'DELETE' })
}

import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { SalaryScale, CreateSalaryScale, SalaryRaiseRound, SalaryRaiseProgress } from '@/types/salary'

export async function fetchSalaryScales(): Promise<ApiResponse<SalaryScale[]>> {
  return apiFetch<SalaryScale[]>('/salary-scales')
}

export async function createSalaryScale(data: CreateSalaryScale): Promise<ApiResponse<SalaryScale>> {
  return apiFetch<SalaryScale>('/salary-scales', { method: 'POST', body: JSON.stringify(data) })
}

export async function deleteSalaryScale(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/salary-scales/${id}`, { method: 'DELETE' })
}

export async function fetchSalaryRaiseRounds(): Promise<ApiResponse<SalaryRaiseRound[]>> {
  return apiFetch<SalaryRaiseRound[]>('/salary-raise-rounds')
}

export async function setIncludeInBudget(
  roundId: number,
  include: boolean,
): Promise<ApiResponse<SalaryRaiseRound[]>> {
  return apiFetch<SalaryRaiseRound[]>(`/salary-raise-rounds/${roundId}/include-in-budget`, {
    method: 'POST',
    body: JSON.stringify({ include_in_budget: include }),
  })
}

export async function fetchRaiseProgress(roundId: number): Promise<ApiResponse<SalaryRaiseProgress[]>> {
  return apiFetch<SalaryRaiseProgress[]>(`/salary-raise-rounds/${roundId}/progress`)
}

export async function markRaiseProgress(
  roundId: number,
  organizationId: number,
  status: 'completed' | 'pending',
  docNo?: string | null,
): Promise<ApiResponse<SalaryRaiseProgress[]>> {
  return apiFetch<SalaryRaiseProgress[]>(`/salary-raise-rounds/${roundId}/progress`, {
    method: 'PUT',
    body: JSON.stringify({ organization_id: organizationId, status, doc_no: docNo ?? null }),
  })
}

export async function seedRaiseProgress(
  roundId: number,
): Promise<ApiResponse<{ created: number; progress: SalaryRaiseProgress[] }>> {
  return apiFetch(`/salary-raise-rounds/${roundId}/progress/seed-all`, { method: 'POST' })
}

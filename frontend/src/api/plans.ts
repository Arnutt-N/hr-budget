import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { Plan, CreatePlan, UpdatePlan } from '@/types/plan'

export async function fetchPlans(page = 1, perPage = 100): Promise<ApiResponse<Plan[]>> {
  return apiFetch<Plan[]>(`/plans?page=${page}&per_page=${perPage}`)
}

export async function fetchPlanById(id: number): Promise<ApiResponse<Plan>> {
  return apiFetch<Plan>(`/plans/${id}`)
}

export async function createPlan(data: CreatePlan): Promise<ApiResponse<Plan>> {
  return apiFetch<Plan>('/plans', { method: 'POST', body: JSON.stringify(data) })
}

export async function updatePlan(id: number, data: UpdatePlan): Promise<ApiResponse<Plan>> {
  return apiFetch<Plan>(`/plans/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deletePlan(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/plans/${id}`, { method: 'DELETE' })
}

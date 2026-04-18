import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  BudgetRequest,
  CreateBudgetRequest,
  ListFilters,
  ListMeta,
  UpdateBudgetRequest,
} from '@/types/budget-request'

export async function fetchRequests(
  filters?: ListFilters,
): Promise<ApiResponse<BudgetRequest[]>> {
  const params = new URLSearchParams()
  if (filters?.status) params.set('status', filters.status)
  if (filters?.fiscal_year) params.set('fiscal_year', String(filters.fiscal_year))
  if (filters?.search) params.set('search', filters.search)
  if (filters?.page) params.set('page', String(filters.page))
  if (filters?.per_page) params.set('per_page', String(filters.per_page))

  const qs = params.toString()
  const path = `/requests${qs ? '?' + qs : ''}`
  return apiFetch<BudgetRequest[]>(path)
}

export async function fetchRequestById(
  id: number,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>(`/requests/${id}`)
}

export async function createRequest(
  data: CreateBudgetRequest,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>('/requests', {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export async function updateRequest(
  id: number,
  data: UpdateBudgetRequest,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>(`/requests/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  })
}

export async function deleteRequest(
  id: number,
): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/requests/${id}`, { method: 'DELETE' })
}

export async function submitRequest(
  id: number,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>(`/requests/${id}/submit`, { method: 'POST' })
}

export async function approveRequest(
  id: number,
  note?: string,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>(`/requests/${id}/approve`, {
    method: 'POST',
    body: JSON.stringify({ note }),
  })
}

export async function rejectRequest(
  id: number,
  note: string,
): Promise<ApiResponse<BudgetRequest>> {
  return apiFetch<BudgetRequest>(`/requests/${id}/reject`, {
    method: 'POST',
    body: JSON.stringify({ note }),
  })
}

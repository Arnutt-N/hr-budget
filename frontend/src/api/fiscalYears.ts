import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { FiscalYear, CreateFiscalYear, UpdateFiscalYear } from '@/types/fiscal-year'

export async function fetchFiscalYears(page = 1, perPage = 50): Promise<ApiResponse<FiscalYear[]>> {
  return apiFetch<FiscalYear[]>(`/fiscal-years?page=${page}&per_page=${perPage}`)
}

export async function fetchFiscalYearById(id: number): Promise<ApiResponse<FiscalYear>> {
  return apiFetch<FiscalYear>(`/fiscal-years/${id}`)
}

export async function createFiscalYear(data: CreateFiscalYear): Promise<ApiResponse<FiscalYear>> {
  return apiFetch<FiscalYear>('/fiscal-years', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateFiscalYear(id: number, data: UpdateFiscalYear): Promise<ApiResponse<FiscalYear>> {
  return apiFetch<FiscalYear>(`/fiscal-years/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteFiscalYear(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/fiscal-years/${id}`, { method: 'DELETE' })
}

export async function setCurrentFiscalYear(id: number): Promise<ApiResponse<FiscalYear>> {
  return apiFetch<FiscalYear>(`/fiscal-years/${id}/set-current`, { method: 'POST' })
}

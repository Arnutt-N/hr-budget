import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  Activity,
  CreateRecordBody,
  CreateSessionBody,
  DisbursementRecord,
  DisbursementRecordDetail,
  DisbursementSession,
  ExpenseType,
  SaveRecordBody,
  SessionFilters,
} from '@/types/disbursement'

export async function fetchSessions(
  filters?: SessionFilters,
): Promise<ApiResponse<DisbursementSession[]>> {
  const params = new URLSearchParams()
  if (filters?.fiscal_year) params.set('fiscal_year', String(filters.fiscal_year))
  if (filters?.organization_id) params.set('organization_id', String(filters.organization_id))
  if (filters?.record_month) params.set('record_month', String(filters.record_month))
  if (filters?.page) params.set('page', String(filters.page))
  if (filters?.per_page) params.set('per_page', String(filters.per_page))

  const qs = params.toString()
  const path = `/disbursement-sessions${qs ? '?' + qs : ''}`
  return apiFetch<DisbursementSession[]>(path)
}

export async function createSession(
  body: CreateSessionBody,
): Promise<ApiResponse<DisbursementSession>> {
  return apiFetch<DisbursementSession>('/disbursement-sessions', {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

export async function fetchSession(
  id: number,
): Promise<ApiResponse<DisbursementSession>> {
  return apiFetch<DisbursementSession>(`/disbursement-sessions/${id}`)
}

export async function deleteSession(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/disbursement-sessions/${id}`, { method: 'DELETE' })
}

export async function fetchActivities(
  sessionId: number,
): Promise<ApiResponse<Activity[]>> {
  return apiFetch<Activity[]>(`/disbursement-sessions/${sessionId}/activities`)
}

export async function createRecord(
  body: CreateRecordBody,
): Promise<ApiResponse<DisbursementRecord>> {
  return apiFetch<DisbursementRecord>('/disbursement-records', {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

export async function fetchRecord(
  id: number,
): Promise<ApiResponse<DisbursementRecordDetail>> {
  return apiFetch<DisbursementRecordDetail>(`/disbursement-records/${id}`)
}

export async function saveRecord(
  id: number,
  body: SaveRecordBody,
): Promise<ApiResponse<DisbursementRecordDetail>> {
  return apiFetch<DisbursementRecordDetail>(`/disbursement-records/${id}`, {
    method: 'PUT',
    body: JSON.stringify(body),
  })
}

export async function fetchExpenseStructure(): Promise<ApiResponse<ExpenseType[]>> {
  return apiFetch<ExpenseType[]>('/expense-structure')
}

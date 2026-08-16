import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  PositionAllowance,
  VacancyRecruitment,
  CreateVacancyRecruitment,
  PersonnelBudgetPolicy,
  CreatePersonnelBudgetPolicy,
  PersonnelAllowance,
  CreatePersonnelAllowance,
  PersonnelAssignment,
  CreatePersonnelAssignment,
  ComputeBudgetResult,
} from '@/types/personnel'

// ---------- position allowances (nested) ----------
export async function fetchPositionAllowances(positionId: number): Promise<ApiResponse<PositionAllowance[]>> {
  return apiFetch<PositionAllowance[]>(`/positions/${positionId}/allowances`)
}

export async function createPositionAllowance(
  positionId: number,
  data: { allowance_type_id: number; effective_from: string; effective_to?: string | null; doc_no?: string | null },
): Promise<ApiResponse<PositionAllowance[]>> {
  return apiFetch<PositionAllowance[]>(`/positions/${positionId}/allowances`, {
    method: 'POST',
    body: JSON.stringify(data),
  })
}

export async function deletePositionAllowance(positionId: number, allowanceId: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/positions/${positionId}/allowances/${allowanceId}`, { method: 'DELETE' })
}

// ---------- vacancy recruitment ----------
export async function fetchVacancyRecruitment(
  page = 1,
  perPage = 50,
  filters: { fiscal_year_id?: string; type?: string } = {},
): Promise<ApiResponse<VacancyRecruitment[]>> {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) })
  if (filters.fiscal_year_id) params.set('fiscal_year_id', filters.fiscal_year_id)
  if (filters.type) params.set('type', filters.type)
  return apiFetch<VacancyRecruitment[]>(`/vacancy-recruitment?${params.toString()}`)
}

export async function createVacancyRecruitment(data: CreateVacancyRecruitment): Promise<ApiResponse<VacancyRecruitment[]>> {
  return apiFetch<VacancyRecruitment[]>('/vacancy-recruitment', { method: 'POST', body: JSON.stringify(data) })
}

export async function deleteVacancyRecruitment(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/vacancy-recruitment/${id}`, { method: 'DELETE' })
}

// ---------- personnel budget policies ----------
export async function fetchPersonnelBudgetPolicies(): Promise<ApiResponse<PersonnelBudgetPolicy[]>> {
  return apiFetch<PersonnelBudgetPolicy[]>('/personnel-budget-policies')
}

export async function createPersonnelBudgetPolicy(data: CreatePersonnelBudgetPolicy): Promise<ApiResponse<PersonnelBudgetPolicy>> {
  return apiFetch<PersonnelBudgetPolicy>('/personnel-budget-policies', { method: 'POST', body: JSON.stringify(data) })
}

export async function updatePersonnelBudgetPolicy(
  id: number,
  data: { vacancy_rule?: string | null; calc_mode?: string; buffer_percent?: number | null; reference_date?: string | null },
): Promise<ApiResponse<PersonnelBudgetPolicy>> {
  return apiFetch<PersonnelBudgetPolicy>(`/personnel-budget-policies/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

// ---------- personnel allowances (actuals) ----------
export async function fetchPersonnelAllowances(
  page = 1,
  perPage = 50,
  filters: { person_id?: string; allowance_type_id?: string } = {},
): Promise<ApiResponse<PersonnelAllowance[]>> {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) })
  if (filters.person_id) params.set('person_id', filters.person_id)
  if (filters.allowance_type_id) params.set('allowance_type_id', filters.allowance_type_id)
  return apiFetch<PersonnelAllowance[]>(`/personnel-allowances?${params.toString()}`)
}

export async function createPersonnelAllowance(data: CreatePersonnelAllowance): Promise<ApiResponse<PersonnelAllowance[]>> {
  return apiFetch<PersonnelAllowance[]>('/personnel-allowances', { method: 'POST', body: JSON.stringify(data) })
}

export async function deletePersonnelAllowance(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/personnel-allowances/${id}`, { method: 'DELETE' })
}

// ---------- personnel assignments ----------
export async function fetchPersonnelAssignments(
  page = 1,
  perPage = 50,
  filters: { person_id?: string; serving_organization_id?: string } = {},
): Promise<ApiResponse<PersonnelAssignment[]>> {
  const params = new URLSearchParams({ page: String(page), per_page: String(perPage) })
  if (filters.person_id) params.set('person_id', filters.person_id)
  if (filters.serving_organization_id) params.set('serving_organization_id', filters.serving_organization_id)
  return apiFetch<PersonnelAssignment[]>(`/personnel-assignments?${params.toString()}`)
}

export async function createPersonnelAssignment(data: CreatePersonnelAssignment): Promise<ApiResponse<PersonnelAssignment[]>> {
  return apiFetch<PersonnelAssignment[]>('/personnel-assignments', { method: 'POST', body: JSON.stringify(data) })
}

export async function deletePersonnelAssignment(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/personnel-assignments/${id}`, { method: 'DELETE' })
}

// ---------- compute ----------
export async function computePersonnelBudget(
  fiscalYearId: number,
  dryRun: boolean,
): Promise<ApiResponse<ComputeBudgetResult>> {
  return apiFetch<ComputeBudgetResult>('/personnel-budget/compute', {
    method: 'POST',
    body: JSON.stringify({ fiscal_year_id: fiscalYearId, dry_run: dryRun }),
  })
}

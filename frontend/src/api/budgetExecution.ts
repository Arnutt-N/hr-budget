import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { ExecutionReport, ExecutionYear } from '@/types/budget-execution'

export async function fetchExecutionReport(
  year?: number,
  org?: number | null,
): Promise<ApiResponse<ExecutionReport>> {
  const params = new URLSearchParams()
  if (year) params.set('year', String(year))
  if (org) params.set('org', String(org))
  const qs = params.toString()
  return apiFetch<ExecutionReport>(`/budget-execution${qs ? `?${qs}` : ''}`)
}

export async function fetchExecutionYears(): Promise<ApiResponse<ExecutionYear[]>> {
  return apiFetch<ExecutionYear[]>(`/budget-execution/years`)
}

/**
 * Direct download URL for the xlsx export. Mirrors vaultFileDownloadUrl: the
 * httpOnly auth cookie rides along on the same-origin GET, so a plain anchor /
 * window.open authenticates without JS reading the token.
 */
export function executionExportUrl(year: number, org?: number | null): string {
  const base = (import.meta.env.VITE_API_BASE_URL as string) || ''
  const params = new URLSearchParams({ year: String(year) })
  if (org) params.set('org', String(org))
  return `${base}/api/v1/budget-execution/export?${params.toString()}`
}

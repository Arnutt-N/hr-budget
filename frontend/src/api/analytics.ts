import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  ComparisonDimension,
  ComparisonReport,
  ForecastReport,
  RequestApprovalReport,
} from '@/types/analytics'

export async function fetchComparison(
  fiscalYear: number,
  dimension: ComparisonDimension,
): Promise<ApiResponse<ComparisonReport>> {
  const params = new URLSearchParams({ fiscal_year: String(fiscalYear), dimension })
  return apiFetch<ComparisonReport>(`/analytics/comparison?${params.toString()}`)
}

export async function fetchForecast(fiscalYear: number): Promise<ApiResponse<ForecastReport>> {
  const params = new URLSearchParams({ fiscal_year: String(fiscalYear) })
  return apiFetch<ForecastReport>(`/analytics/forecast?${params.toString()}`)
}

export async function fetchRequestVsApproved(
  fiscalYear: number,
): Promise<ApiResponse<RequestApprovalReport>> {
  const params = new URLSearchParams({ fiscal_year: String(fiscalYear) })
  return apiFetch<RequestApprovalReport>(`/analytics/request-vs-approved?${params.toString()}`)
}

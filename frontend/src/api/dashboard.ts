import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { DashboardSummary, MonthlyChart } from '@/types/dashboard'

export async function fetchDashboardSummary(year?: number): Promise<ApiResponse<DashboardSummary>> {
  const query = year ? `?year=${year}` : ''
  return apiFetch<DashboardSummary>(`/dashboard/summary${query}`)
}

export async function fetchMonthlyChart(year?: number): Promise<ApiResponse<MonthlyChart>> {
  const query = year ? `?year=${year}` : ''
  return apiFetch<MonthlyChart>(`/dashboard/chart-data${query}`)
}

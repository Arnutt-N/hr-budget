import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { Position, CreatePosition, UpdatePosition, PositionVersion, CreatePositionVersion } from '@/types/position'

export interface PositionFilters {
  organization_id?: number
  employee_category?: string
  occupancy?: string
  approval_status?: string
  q?: string
}

export async function fetchPositions(filters: PositionFilters = {}): Promise<ApiResponse<Position[]>> {
  const params = new URLSearchParams({ per_page: '100' })
  for (const [key, value] of Object.entries(filters)) {
    if (value !== undefined && value !== null && value !== '') params.set(key, String(value))
  }
  return apiFetch<Position[]>(`/positions?${params.toString()}`)
}

export async function fetchPositionById(id: number): Promise<ApiResponse<Position>> {
  return apiFetch<Position>(`/positions/${id}`)
}

export async function createPosition(data: CreatePosition): Promise<ApiResponse<Position>> {
  return apiFetch<Position>('/positions', { method: 'POST', body: JSON.stringify(data) })
}

export async function updatePosition(id: number, data: UpdatePosition): Promise<ApiResponse<Position>> {
  return apiFetch<Position>(`/positions/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deletePosition(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/positions/${id}`, { method: 'DELETE' })
}

export async function fetchPositionVersions(id: number): Promise<ApiResponse<PositionVersion[]>> {
  return apiFetch<PositionVersion[]>(`/positions/${id}/versions`)
}

export async function createPositionVersion(
  id: number,
  data: CreatePositionVersion,
): Promise<ApiResponse<PositionVersion[]>> {
  return apiFetch<PositionVersion[]>(`/positions/${id}/versions`, { method: 'POST', body: JSON.stringify(data) })
}

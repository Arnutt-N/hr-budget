import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { Organization, CreateOrganization, UpdateOrganization } from '@/types/organization'

export async function fetchOrganizations(page = 1, perPage = 100): Promise<ApiResponse<Organization[]>> {
  return apiFetch<Organization[]>(`/organizations?page=${page}&per_page=${perPage}`)
}

export async function fetchOrganizationById(id: number): Promise<ApiResponse<Organization>> {
  return apiFetch<Organization>(`/organizations/${id}`)
}

export async function createOrganization(data: CreateOrganization): Promise<ApiResponse<Organization>> {
  return apiFetch<Organization>('/organizations', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateOrganization(id: number, data: UpdateOrganization): Promise<ApiResponse<Organization>> {
  return apiFetch<Organization>(`/organizations/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteOrganization(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/organizations/${id}`, { method: 'DELETE' })
}

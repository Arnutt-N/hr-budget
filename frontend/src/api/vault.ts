import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { VaultFolder, VaultFile, FolderListResponse, VaultYear } from '@/types/vault'

export interface CreateFolderPayload {
  name: string
  parent_id?: number | null
  fiscal_year?: number | null
  description?: string | null
}

export async function fetchYears(): Promise<ApiResponse<VaultYear[]>> {
  return apiFetch<VaultYear[]>(`/vault/years`)
}

export async function fetchFolders(
  year: number,
  parentId: number | null,
): Promise<ApiResponse<FolderListResponse>> {
  const parentQuery = parentId !== null ? `&parent=${parentId}` : ''
  return apiFetch<FolderListResponse>(`/vault/folders?year=${year}${parentQuery}`)
}

export async function fetchFiles(folderId: number): Promise<ApiResponse<VaultFile[]>> {
  return apiFetch<VaultFile[]>(`/vault/folders/${folderId}/files`)
}

export async function createFolder(payload: CreateFolderPayload): Promise<ApiResponse<VaultFolder>> {
  return apiFetch<VaultFolder>(`/vault/folders`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function deleteFolder(id: number): Promise<ApiResponse<null>> {
  return apiFetch<null>(`/vault/folders/${id}`, { method: 'DELETE' })
}

export interface InitializeYearResult {
  fiscal_year: number
  created: number
}

/** Scaffold a fiscal year's standard (system) folders from budget categories. */
export async function initializeVaultYear(
  year: number,
): Promise<ApiResponse<InitializeYearResult>> {
  return apiFetch<InitializeYearResult>(`/vault/years`, {
    method: 'POST',
    body: JSON.stringify({ fiscal_year: year }),
  })
}

export async function uploadVaultFile(folderId: number, file: File): Promise<ApiResponse<VaultFile>> {
  const formData = new FormData()
  formData.append('file', file)
  return apiFetch<VaultFile>(`/vault/folders/${folderId}/files`, {
    method: 'POST',
    body: formData,
  }, true)
}

export async function deleteVaultFile(id: number): Promise<ApiResponse<null>> {
  return apiFetch<null>(`/vault/files/${id}`, { method: 'DELETE' })
}

export function vaultFileDownloadUrl(id: number): string {
  const base = (import.meta.env.VITE_API_BASE_URL as string) || ''
  return `${base}/api/v1/vault/files/${id}/download`
}

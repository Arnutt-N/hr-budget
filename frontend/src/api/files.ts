import { apiFetch } from '@/composables/useApi'
import type { FileAttachment } from '@/types/file'
import type { ApiResponse } from '@/types/api'

export async function fetchRequestFiles(requestId: number): Promise<ApiResponse<FileAttachment[]>> {
  return apiFetch<FileAttachment[]>(`/requests/${requestId}/files`)
}

export async function uploadRequestFile(requestId: number, file: File): Promise<ApiResponse<FileAttachment>> {
  const formData = new FormData()
  formData.append('file', file)
  return apiFetch<FileAttachment>(`/requests/${requestId}/files`, {
    method: 'POST',
    body: formData,
  }, true)
}

export async function deleteFile(fileId: number): Promise<ApiResponse<null>> {
  return apiFetch<null>(`/files/${fileId}`, { method: 'DELETE' })
}

export function fileDownloadUrl(fileId: number): string {
  const base = (import.meta.env.VITE_API_BASE_URL as string) || ''
  return `${base}/api/v1/files/${fileId}/download`
}

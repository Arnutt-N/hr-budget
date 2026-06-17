import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type { ApprovalLevel, ApprovalStatus, ApprovalActionResult } from '@/types/rbac'

/** Configured chain levels (กอง → กรม → กระทรวง). */
export async function fetchApprovalLevels(): Promise<ApiResponse<ApprovalLevel[]>> {
  return apiFetch<ApprovalLevel[]>('/approval-levels')
}

/** Current chain state + history of a request. */
export async function fetchApprovalStatus(
  requestId: number,
): Promise<ApiResponse<ApprovalStatus>> {
  return apiFetch<ApprovalStatus>(`/requests/${requestId}/approval`)
}

export async function approveStep(
  requestId: number,
  note?: string,
): Promise<ApiResponse<ApprovalActionResult>> {
  return apiFetch<ApprovalActionResult>(`/requests/${requestId}/approval/approve`, {
    method: 'POST',
    body: JSON.stringify({ note: note ?? null }),
  })
}

export async function rejectStep(
  requestId: number,
  note: string,
): Promise<ApiResponse<ApprovalActionResult>> {
  return apiFetch<ApprovalActionResult>(`/requests/${requestId}/approval/reject`, {
    method: 'POST',
    body: JSON.stringify({ note }),
  })
}

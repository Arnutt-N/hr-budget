import { apiFetch } from '@/composables/useApi'
import type { Notification, UnreadCountResponse } from '@/types/notification'
import type { ApiResponse } from '@/types/api'

export async function fetchNotifications(page = 1, perPage = 20): Promise<ApiResponse<Notification[]>> {
  return apiFetch<Notification[]>(`/notifications?page=${page}&per_page=${perPage}`)
}

export async function fetchUnreadCount(): Promise<ApiResponse<UnreadCountResponse>> {
  return apiFetch<UnreadCountResponse>('/notifications/unread-count')
}

export async function markNotificationRead(id: number): Promise<ApiResponse<{ marked_read: boolean }>> {
  return apiFetch<{ marked_read: boolean }>(`/notifications/${id}/read`, { method: 'POST' })
}

export async function markAllNotificationsRead(): Promise<ApiResponse<{ marked_all_read: boolean }>> {
  return apiFetch<{ marked_all_read: boolean }>('/notifications/read-all', { method: 'POST' })
}

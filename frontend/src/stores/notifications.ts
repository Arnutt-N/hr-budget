import { ref } from 'vue'
import { defineStore } from 'pinia'
import {
  fetchNotifications,
  fetchUnreadCount,
  markNotificationRead,
  markAllNotificationsRead,
} from '@/api/notifications'
import type { Notification } from '@/types/notification'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const meta = ref<{ total: number; page: number; per_page: number; total_pages: number } | null>(null)

  async function fetchList(page = 1): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const result = await fetchNotifications(page)
      if (result.success && result.data) {
        notifications.value = result.data
        meta.value = (result.meta as { total: number; page: number; per_page: number; total_pages: number } | undefined) ?? null
        return true
      }
      error.value = result.error ?? 'เกิดข้อผิดพลาด'
      return false
    } catch {
      error.value = 'ไม่สามารถโหลดการแจ้งเตือนได้'
      return false
    } finally {
      loading.value = false
    }
  }

  async function refreshUnreadCount(): Promise<void> {
    try {
      const result = await fetchUnreadCount()
      if (result.success && result.data) {
        unreadCount.value = result.data.unread_count
      }
    } catch {
      // silent — polling should not spam errors
    }
  }

  async function markRead(id: number): Promise<boolean> {
    try {
      const result = await markNotificationRead(id)
      if (result.success) {
        notifications.value = notifications.value.map((n) =>
          n.id === id ? { ...n, is_read: true } : n
        )
        unreadCount.value = Math.max(0, unreadCount.value - 1)
        return true
      }
      return false
    } catch {
      return false
    }
  }

  async function markAllRead(): Promise<boolean> {
    try {
      const result = await markAllNotificationsRead()
      if (result.success) {
        notifications.value = notifications.value.map((n) => ({ ...n, is_read: true }))
        unreadCount.value = 0
        return true
      }
      return false
    } catch {
      return false
    }
  }

  return { notifications, unreadCount, loading, error, meta, fetchList, refreshUnreadCount, markRead, markAllRead }
})

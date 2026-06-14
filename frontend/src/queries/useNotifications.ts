import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { MaybeRefOrGetter } from 'vue'
import {
  fetchNotifications,
  fetchUnreadCount,
  markNotificationRead,
  markAllNotificationsRead,
} from '@/api/notifications'

const LIST_KEY = ['notifications'] as const
const UNREAD_KEY = ['notifications', 'unread'] as const

/** Unread badge count — polls every 60s (replaces the bell's manual setInterval). */
export function useUnreadCount() {
  return useQuery({
    queryKey: UNREAD_KEY,
    queryFn: async () => {
      const res = await fetchUnreadCount()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดจำนวนแจ้งเตือนไม่สำเร็จ')
      return res.data.unread_count
    },
    refetchInterval: 60_000,
  })
}

/** Notification list. Pass a reactive `enabled` so the bell only fetches when open. */
export function useNotificationList(enabled: MaybeRefOrGetter<boolean> = true) {
  return useQuery({
    queryKey: LIST_KEY,
    queryFn: async () => {
      const res = await fetchNotifications()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดการแจ้งเตือนไม่สำเร็จ')
      return res.data
    },
    enabled,
  })
}

export function useMarkRead() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await markNotificationRead(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถทำเครื่องหมายอ่านได้')
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: LIST_KEY })
      qc.invalidateQueries({ queryKey: UNREAD_KEY })
    },
  })
}

export function useMarkAllRead() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async () => {
      const res = await markAllNotificationsRead()
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถทำเครื่องหมายอ่านทั้งหมดได้')
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: LIST_KEY })
      qc.invalidateQueries({ queryKey: UNREAD_KEY })
    },
  })
}

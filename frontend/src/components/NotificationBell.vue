<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Bell } from '@lucide/vue'
import {
  useUnreadCount,
  useNotificationList,
  useMarkRead,
  useMarkAllRead,
} from '@/queries/useNotifications'

const router = useRouter()
const open = ref(false)

const unreadQuery = useUnreadCount()
const listQuery = useNotificationList(open) // lazy: fetches only while the dropdown is open
const markRead = useMarkRead()
const markAllRead = useMarkAllRead()

const unreadCount = computed(() => unreadQuery.data.value ?? 0)
const notifications = computed(() => listQuery.data.value ?? [])

function toggleDropdown() {
  open.value = !open.value
}

function close() {
  open.value = false
}

async function handleClick(id: number, link: string | null) {
  await markRead.mutateAsync(id)
  open.value = false
  if (link && link.startsWith('/')) router.push(link)
}

async function handleMarkAllRead() {
  await markAllRead.mutateAsync()
}

function viewAll() {
  open.value = false
  router.push('/notifications')
}

function timeAgo(dateStr: string): string {
  const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
  if (seconds < 60) return 'เมื่อสักครู่'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} นาทีที่แล้ว`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} ชั่วโมงที่แล้ว`
  return `${Math.floor(seconds / 86400)} วันที่แล้ว`
}

function typeIcon(type: string): string {
  if (type === 'approved') return '✓'
  if (type === 'rejected') return '✗'
  return '✉'
}
</script>

<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative rounded-full p-2 text-dark-muted hover:bg-slate-800 hover:text-white"
      title="การแจ้งเตือน"
      aria-label="การแจ้งเตือน"
    >
      <Bell class="h-5 w-5" />
      <span
        v-if="unreadCount > 0"
        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Overlay to close on outside click -->
    <div v-if="open" class="fixed inset-0 z-30" @click="close" />

    <!-- Dropdown -->
    <div
      v-if="open"
      class="absolute right-0 z-40 mt-2 w-80 rounded-lg border border-dark-border bg-dark-card shadow-lg"
    >
      <div class="flex items-center justify-between border-b border-dark-border px-4 py-3">
        <span class="text-sm font-medium text-white">การแจ้งเตือน</span>
        <button
          v-if="unreadCount > 0"
          @click="handleMarkAllRead"
          class="text-xs text-primary-400 hover:text-primary-500"
        >
          อ่านทั้งหมด
        </button>
      </div>

      <div class="max-h-80 overflow-y-auto">
        <div v-if="listQuery.isLoading.value" class="px-4 py-8 text-center text-sm text-dark-muted">
          กำลังโหลด...
        </div>
        <div
          v-else-if="notifications.length === 0"
          class="px-4 py-8 text-center text-sm text-dark-muted"
        >
          ไม่มีการแจ้งเตือน
        </div>
        <div v-else>
          <button
            v-for="n in notifications"
            :key="n.id"
            @click="handleClick(n.id, n.link)"
            class="w-full border-b border-dark-border px-4 py-3 text-left transition hover:bg-slate-800/50"
            :class="{ 'bg-sky-500/10': !n.is_read }"
          >
            <div class="flex items-start gap-2">
              <span class="mt-0.5 text-sm">{{ typeIcon(n.type) }}</span>
              <div class="min-w-0 flex-1">
                <p class="text-sm text-white">{{ n.title }}</p>
                <p v-if="n.message" class="truncate text-xs text-dark-muted">{{ n.message }}</p>
                <p class="mt-1 text-[11px] text-dark-muted">{{ timeAgo(n.created_at) }}</p>
              </div>
            </div>
          </button>
        </div>
      </div>

      <button
        @click="viewAll"
        class="w-full border-t border-dark-border px-4 py-3 text-center text-xs text-primary-400 hover:bg-slate-800/50 hover:text-primary-500"
      >
        ดูการแจ้งเตือนทั้งหมด
      </button>
    </div>
  </div>
</template>

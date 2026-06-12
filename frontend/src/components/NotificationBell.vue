<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'

const router = useRouter()
const store = useNotificationStore()
const open = ref(false)
let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  store.refreshUnreadCount()
  pollTimer = setInterval(() => store.refreshUnreadCount(), 60_000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})

async function toggleDropdown() {
  if (!open.value) {
    await store.fetchList()
  }
  open.value = !open.value
}

function close() {
  open.value = false
}

async function handleClick(id: number, link: string | null) {
  await store.markRead(id)
  open.value = false
  if (link && link.startsWith('/')) router.push(link)
}

async function handleMarkAllRead() {
  await store.markAllRead()
}

function timeAgo(dateStr: string): string {
  const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
  if (seconds < 60) return 'เมื่อสักครู่'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} นาทีที่แล้ว`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} ชั่วโมงที่แล้ว`
  return `${Math.floor(seconds / 86400)} วันที่แล้ว`
}

function typeIcon(type: string): string {
  if (type === 'approved') return '\u2713'
  if (type === 'rejected') return '\u2717'
  return '\u2709'
}
</script>

<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative rounded-full p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900"
      title="การแจ้งเตือน"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="store.unreadCount > 0"
        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
      >
        {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
      </span>
    </button>

    <!-- Overlay to close on outside click -->
    <div v-if="open" class="fixed inset-0 z-30" @click="close" />

    <!-- Dropdown -->
    <div
      v-if="open"
      class="absolute right-0 z-40 mt-2 w-80 rounded-lg border bg-white shadow-lg"
    >
      <div class="flex items-center justify-between border-b px-4 py-3">
        <span class="font-medium text-gray-900 text-sm">การแจ้งเตือน</span>
        <button
          v-if="store.unreadCount > 0"
          @click="handleMarkAllRead"
          class="text-xs text-blue-600 hover:text-blue-800"
        >
          อ่านทั้งหมด
        </button>
      </div>

      <div class="max-h-80 overflow-y-auto">
        <div v-if="store.loading" class="px-4 py-8 text-center text-gray-500 text-sm">กำลังโหลด...</div>
        <div v-else-if="store.notifications.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
          ไม่มีการแจ้งเตือน
        </div>
        <div v-else>
          <button
            v-for="n in store.notifications"
            :key="n.id"
            @click="handleClick(n.id, n.link)"
            class="w-full border-b px-4 py-3 text-left transition hover:bg-gray-50"
            :class="{ 'bg-blue-50': !n.is_read }"
          >
            <div class="flex items-start gap-2">
              <span class="mt-0.5 text-sm">{{ typeIcon(n.type) }}</span>
              <div class="min-w-0 flex-1">
                <p class="text-sm text-gray-900">{{ n.title }}</p>
                <p v-if="n.message" class="truncate text-xs text-gray-500">{{ n.message }}</p>
                <p class="mt-1 text-[11px] text-gray-400">{{ timeAgo(n.created_at) }}</p>
              </div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

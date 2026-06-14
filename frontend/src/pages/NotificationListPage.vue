<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { CheckCheck, Inbox } from '@lucide/vue'
import {
  useNotificationList,
  useUnreadCount,
  useMarkRead,
  useMarkAllRead,
} from '@/queries/useNotifications'

const router = useRouter()
const listQuery = useNotificationList(true)
const unreadQuery = useUnreadCount()
const markRead = useMarkRead()
const markAllRead = useMarkAllRead()

const notifications = computed(() => listQuery.data.value ?? [])
const unreadCount = computed(() => unreadQuery.data.value ?? 0)

async function open(id: number, link: string | null, isRead: boolean) {
  if (!isRead) await markRead.mutateAsync(id)
  if (link && link.startsWith('/')) router.push(link)
}

async function markAll() {
  await markAllRead.mutateAsync()
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleString('th-TH', { dateStyle: 'medium', timeStyle: 'short' })
}

function typeIcon(type: string): string {
  if (type === 'approved') return '✓'
  if (type === 'rejected') return '✗'
  return '✉'
}
</script>

<template>
  <div class="mx-auto max-w-3xl space-y-5">
    <header class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-white">การแจ้งเตือน</h1>
        <p class="mt-1 text-sm text-dark-muted">
          <span v-if="unreadCount > 0">ยังไม่ได้อ่าน {{ unreadCount }} รายการ</span>
          <span v-else>อ่านครบทุกรายการแล้ว</span>
        </p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAll"
        :disabled="markAllRead.isPending.value"
        class="inline-flex items-center gap-2 rounded-lg border border-dark-border bg-dark-card px-3 py-2 text-sm text-primary-400 transition hover:border-slate-600 hover:text-primary-500 disabled:opacity-50"
      >
        <CheckCheck class="h-4 w-4" />
        อ่านทั้งหมด
      </button>
    </header>

    <!-- Loading -->
    <div v-if="listQuery.isLoading.value" class="space-y-3">
      <div
        v-for="n in 4"
        :key="n"
        class="h-20 animate-pulse rounded-xl border border-dark-border bg-dark-card"
      />
    </div>

    <!-- Error -->
    <div
      v-else-if="listQuery.isError.value"
      class="rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"
    >
      โหลดการแจ้งเตือนไม่สำเร็จ — {{ (listQuery.error.value as Error | null)?.message }}
    </div>

    <!-- Empty -->
    <div
      v-else-if="notifications.length === 0"
      class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-dark-border bg-dark-card py-16 text-dark-muted"
    >
      <Inbox class="h-10 w-10" />
      <p class="text-sm">ยังไม่มีการแจ้งเตือน</p>
    </div>

    <!-- List -->
    <ul v-else class="space-y-3">
      <li v-for="n in notifications" :key="n.id">
        <button
          @click="open(n.id, n.link, n.is_read)"
          class="flex w-full items-start gap-3 rounded-xl border border-dark-border bg-dark-card p-4 text-left shadow-sm transition hover:border-slate-600 hover:shadow-md"
          :class="{ 'border-l-4 border-l-primary-500': !n.is_read }"
        >
          <span
            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-700/50 text-sm text-white"
          >
            {{ typeIcon(n.type) }}
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <p class="truncate font-medium text-white">{{ n.title }}</p>
              <span
                v-if="!n.is_read"
                class="shrink-0 rounded-full bg-primary-500/20 px-2 py-0.5 text-[10px] font-semibold text-primary-300"
              >
                ใหม่
              </span>
            </div>
            <p v-if="n.message" class="mt-1 text-sm text-dark-muted">{{ n.message }}</p>
            <p class="mt-1 text-xs text-dark-muted">{{ formatDate(n.created_at) }}</p>
          </div>
        </button>
      </li>
    </ul>
  </div>
</template>

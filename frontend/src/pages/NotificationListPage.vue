<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { CheckCheck, Inbox } from '@lucide/vue'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import {
  useNotificationList,
  useUnreadCount,
  useMarkRead,
  useMarkAllRead,
} from '@/queries/useNotifications'

const router = useRouter()
const toast = useToast()
const listQuery = useNotificationList(true)
const unreadQuery = useUnreadCount()
const markRead = useMarkRead()
const markAllRead = useMarkAllRead()

const notifications = computed(() => listQuery.data.value ?? [])
const unreadCount = computed(() => unreadQuery.data.value ?? 0)
const pageSubtitle = computed(() =>
  unreadCount.value > 0 ? `ยังไม่ได้อ่าน ${unreadCount.value} รายการ` : 'อ่านครบทุกรายการแล้ว',
)

async function open(id: number, link: string | null, isRead: boolean) {
  try {
    if (!isRead) await markRead.mutateAsync(id)
  } catch {
    toast.add({ severity: 'error', summary: 'ทำรายการไม่สำเร็จ', detail: 'ลองใหม่อีกครั้ง', life: 5000 })
    return
  }
  if (link && link.startsWith('/')) router.push(link)
}

async function markAll() {
  try {
    await markAllRead.mutateAsync()
  } catch {
    toast.add({ severity: 'error', summary: 'ทำรายการไม่สำเร็จ', detail: 'ลองใหม่อีกครั้ง', life: 5000 })
  }
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
    <PageHeader title="การแจ้งเตือน" :subtitle="pageSubtitle">
      <button
        v-if="unreadCount > 0"
        @click="markAll"
        :disabled="markAllRead.isPending.value"
        class="inline-flex items-center gap-2 rounded-lg border border-dark-border bg-dark-card px-3 py-2 text-sm text-primary-400 transition hover:border-slate-600 hover:text-primary-500 disabled:opacity-50"
      >
        <CheckCheck aria-hidden="true" class="h-4 w-4" />
        อ่านทั้งหมด
      </button>
    </PageHeader>

    <!-- Loading -->
    <div v-if="listQuery.isLoading.value" class="space-y-3">
      <div
        v-for="n in 4"
        :key="n"
        class="h-20 animate-pulse rounded-xl border border-dark-border bg-dark-card"
      />
    </div>

    <!-- Error -->
    <QueryErrorState v-else-if="listQuery.isError.value" :error="listQuery.error.value" :retry="() => listQuery.refetch()" />

    <!-- Empty -->
    <div
      v-else-if="notifications.length === 0"
      class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-dark-border bg-dark-card py-16 text-dark-muted"
    >
      <Inbox aria-hidden="true" class="h-10 w-10" />
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
              <span class="truncate font-medium text-white">{{ n.title }}</span>
              <span
                v-if="!n.is_read"
                class="shrink-0 rounded-full bg-primary-500/20 px-2 py-0.5 text-[10px] font-semibold text-primary-400"
              >
                ใหม่
              </span>
            </div>
            <span v-if="n.message" class="mt-1 break-words text-sm text-dark-muted">{{ n.message }}</span>
            <span class="mt-1 text-xs text-dark-muted">{{ formatDate(n.created_at) }}</span>
          </div>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, toRef } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Message from 'primevue/message'
import {
  useApprovalLevels,
  useApprovalStatus,
  useApproveStep,
  useRejectStep,
} from '@/queries/useApprovalChain'
import { useMyPermissions } from '@/queries/usePermissions'
import { canActOnChain, approvalActionLabel } from '@/lib/rbac'

const props = defineProps<{ requestId: number }>()
const requestId = toRef(props, 'requestId')
const toast = useToast()

const levelsQuery = useApprovalLevels()
const statusQuery = useApprovalStatus(requestId)
const permsQuery = useMyPermissions()
const approveMut = useApproveStep(requestId)
const rejectMut = useRejectStep(requestId)

const levels = computed(() => levelsQuery.data.value ?? [])
const status = computed(() => statusQuery.data.value ?? null)
const permissions = computed(() => permsQuery.data.value?.permissions ?? [])
const history = computed(() => status.value?.history ?? [])

// Only render the panel when the request is actually in / has been through a chain.
const inChain = computed(
  () => !!status.value && (status.value.current_level !== null || history.value.length > 0),
)

const canAct = computed(() => canActOnChain(permissions.value, status.value))
const busy = computed(() => approveMut.isPending.value || rejectMut.isPending.value)

const rejectMode = ref(false)
const note = ref('')
const errorMsg = ref('')

/** Visual state of a level relative to the request's current position. */
function levelState(levelNo: number): 'done' | 'current' | 'pending' {
  const s = status.value
  if (!s) return 'pending'
  if (s.request_status === 'approved') return 'done'
  if (s.current_level === null) return 'pending'
  if (levelNo < s.current_level) return 'done'
  if (levelNo === s.current_level) return 'current'
  return 'pending'
}

function levelClasses(levelNo: number): string {
  switch (levelState(levelNo)) {
    case 'done':
      return 'border-green-500/40 bg-green-500/10 text-green-300'
    case 'current':
      return 'border-primary-500 bg-primary-500/15 text-primary-200'
    default:
      return 'border-dark-border bg-dark-bg text-dark-muted'
  }
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function levelName(levelNo: number | null): string {
  if (levelNo === null) return '-'
  return levels.value.find((l) => l.level === levelNo)?.name_th ?? `ระดับ ${levelNo}`
}

async function onApprove(): Promise<void> {
  errorMsg.value = ''
  try {
    await approveMut.mutateAsync(note.value.trim() || undefined)
    note.value = ''
    toast.add({ severity: 'success', summary: 'อนุมัติขั้นนี้แล้ว', life: 3000 })
  } catch (e: unknown) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถอนุมัติได้'
  }
}

async function onReject(): Promise<void> {
  if (!note.value.trim()) {
    errorMsg.value = 'กรุณาระบุเหตุผลการปฏิเสธ'
    return
  }
  errorMsg.value = ''
  try {
    await rejectMut.mutateAsync(note.value.trim())
    note.value = ''
    rejectMode.value = false
    toast.add({ severity: 'success', summary: 'ปฏิเสธคำขอแล้ว', life: 3000 })
  } catch (e: unknown) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถปฏิเสธได้'
  }
}
</script>

<template>
  <div
    v-if="inChain"
    class="mb-6 rounded-lg border border-dark-border bg-dark-card p-6 shadow"
  >
    <h3 class="mb-4 text-sm font-semibold text-dark-muted">สายอนุมัติหลายชั้น</h3>

    <!-- Level stepper -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
      <template v-for="(lvl, idx) in levels" :key="lvl.id">
        <div
          class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm"
          :class="levelClasses(lvl.level)"
        >
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full border border-current text-xs font-semibold"
          >
            {{ lvl.level }}
          </span>
          <span>{{ lvl.name_th }}</span>
        </div>
        <span v-if="idx < levels.length - 1" class="text-dark-muted">→</span>
      </template>
    </div>

    <p v-if="status?.request_status === 'pending' && status.current_level !== null" class="mb-3 text-sm">
      <span class="text-dark-muted">รออนุมัติที่:</span>
      <span class="ml-1 font-medium text-primary-300">{{ levelName(status.current_level) }}</span>
    </p>
    <p v-else-if="status?.request_status === 'approved'" class="mb-3 text-sm text-green-400">
      อนุมัติครบทุกขั้นแล้ว
    </p>
    <p v-else-if="status?.request_status === 'rejected'" class="mb-3 text-sm text-red-400">
      คำขอถูกปฏิเสธ
    </p>

    <Message v-if="errorMsg" severity="error" :closable="false" class="mb-3">{{ errorMsg }}</Message>

    <!-- Actions (visibility by permission; backend enforces level + scope) -->
    <div v-if="canAct" class="border-t border-dark-border pt-4">
      <div v-if="!rejectMode" class="flex flex-wrap gap-2">
        <Button label="อนุมัติขั้นนี้" icon="pi pi-check" severity="success" :loading="busy" @click="onApprove" />
        <Button label="ปฏิเสธ" icon="pi pi-times" severity="danger" outlined :disabled="busy" @click="rejectMode = true" />
      </div>
      <div v-else class="space-y-2">
        <label class="block text-sm font-medium text-red-400">เหตุผลการปฏิเสธ *</label>
        <textarea
          v-model="note"
          rows="3"
          class="w-full rounded border border-dark-border bg-dark-bg px-3 py-2 text-sm text-dark-text focus:border-red-500 focus:outline-none"
          placeholder="ระบุเหตุผล..."
        ></textarea>
        <div class="flex gap-2">
          <Button label="ยืนยันปฏิเสธ" severity="danger" :loading="busy" @click="onReject" />
          <Button label="ยกเลิก" severity="secondary" text :disabled="busy" @click="rejectMode = false; note = ''" />
        </div>
      </div>
      <p class="mt-2 text-xs text-dark-muted">
        ระบบจะตรวจสอบสิทธิ์ตามระดับและหน่วยงานที่คุณรับผิดชอบอีกครั้ง
      </p>
    </div>

    <!-- History -->
    <div v-if="history.length" class="mt-4 border-t border-dark-border pt-4">
      <h4 class="mb-2 text-xs font-semibold text-dark-muted">ประวัติการอนุมัติตามขั้น</h4>
      <div class="space-y-2">
        <div
          v-for="item in history"
          :key="item.id"
          class="flex items-start gap-3 border-l-2 border-dark-border py-1 pl-3 text-sm"
        >
          <span
            class="rounded px-2 py-0.5 text-xs"
            :class="item.action === 'rejected' ? 'bg-red-500/10 text-red-400' : 'bg-green-500/10 text-green-400'"
          >
            {{ approvalActionLabel(item.action) }}
          </span>
          <div>
            <p class="text-dark-text">{{ levelName(item.level) }}</p>
            <p v-if="item.note" class="text-dark-muted">{{ item.note }}</p>
            <p class="text-xs text-dark-muted">{{ formatDate(item.created_at) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

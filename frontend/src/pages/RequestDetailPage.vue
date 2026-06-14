<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import {
  useBudgetRequest,
  useSubmitBudgetRequest,
  useApproveBudgetRequest,
  useRejectBudgetRequest,
} from '@/queries/useBudgetRequests'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/StatusBadge.vue'
import FileUploader from '@/components/FileUploader.vue'

const route = useRoute()
const auth = useAuthStore()
const requestId = computed(() => Number(route.params.id))
const requestQuery = useBudgetRequest(requestId)
const submitMut = useSubmitBudgetRequest()
const approveMut = useApproveBudgetRequest()
const rejectMut = useRejectBudgetRequest()

const rejectMode = ref(false)
const rejectNote = ref('')
const errorMsg = ref('')

const req = computed(() => requestQuery.data.value ?? null)
const isOwner = computed(() => !!req.value && !!auth.user && req.value.created_by === auth.user.id)
const isAdmin = computed(() => auth.user?.role === 'admin')
const canEdit = computed(() => isOwner.value && (req.value?.request_status === 'draft' || req.value?.request_status === 'saved'))
const canSubmit = computed(() => isOwner.value && (req.value?.request_status === 'draft' || req.value?.request_status === 'saved'))
const canApprove = computed(() => req.value?.request_status === 'pending')
// Backend gates approve/reject to admins only — mirror that in the UI.
const showApproveReject = computed(() => canApprove.value && isAdmin.value)

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function formatAmount(amount: string | null): string {
  if (!amount) return '-'
  return parseFloat(amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })
}

const actionLabels: Record<string, string> = {
  created: 'สร้าง',
  modified: 'แก้ไข',
  submitted: 'ส่งอนุมัติ',
  approved: 'อนุมัติ',
  rejected: 'ปฏิเสธ',
  deleted: 'ลบ',
}

async function handleSubmit() {
  errorMsg.value = ''
  try {
    await submitMut.mutateAsync(req.value!.id)
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถส่งอนุมัติได้'
  }
}

async function handleApprove() {
  errorMsg.value = ''
  try {
    await approveMut.mutateAsync({ id: req.value!.id })
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถอนุมัติได้'
  }
}

async function handleReject() {
  if (!rejectNote.value.trim()) {
    errorMsg.value = 'กรุณาระบุเหตุผลการปฏิเสธ'
    return
  }
  errorMsg.value = ''
  try {
    await rejectMut.mutateAsync({ id: req.value!.id, note: rejectNote.value.trim() })
    rejectMode.value = false
    rejectNote.value = ''
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถปฏิเสธได้'
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">รายละเอียดคำขอ</h1>
      <router-link to="/requests" class="text-sm text-dark-muted hover:text-dark-text">
        &larr; กลับ
      </router-link>
    </div>

    <div v-if="requestQuery.isLoading.value" class="py-16 text-center text-dark-muted">กำลังโหลด...</div>

    <div v-else-if="!req" class="rounded-lg bg-dark-card border border-dark-border py-16 text-center shadow">
      <p class="text-dark-muted">ไม่พบคำของบประมาณ</p>
    </div>

    <template v-else>
      <div v-if="errorMsg" class="mb-4 rounded bg-red-500/10 p-3 text-sm text-red-400" role="alert">
        {{ errorMsg }}
      </div>

      <!-- Request info -->
      <div class="mb-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-lg font-semibold text-white">{{ req.request_title }}</h2>
            <p class="mt-1 text-sm text-dark-muted">
              ปีงบ {{ req.fiscal_year }}
              <span v-if="req.org_name"> · {{ req.org_name }}</span>
            </p>
          </div>
          <StatusBadge :status="req.request_status" />
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
          <div>
            <span class="text-dark-muted">ยอดรวม</span>
            <p class="font-semibold text-white">{{ formatAmount(req.total_amount) }} บาท</p>
          </div>
          <div>
            <span class="text-dark-muted">ผู้สร้าง</span>
            <p class="text-white">{{ req.created_by_name || '-' }}</p>
          </div>
          <div>
            <span class="text-dark-muted">วันที่สร้าง</span>
            <p class="text-white">{{ formatDate(req.created_at) }}</p>
          </div>
          <div>
            <span class="text-dark-muted">วันที่ส่ง</span>
            <p class="text-white">{{ formatDate(req.submitted_at) }}</p>
          </div>
        </div>

        <div v-if="req.rejected_reason" class="mt-4 rounded bg-red-500/10 p-3 text-sm text-red-400">
          <strong>เหตุผลการปฏิเสธ:</strong> {{ req.rejected_reason }}
        </div>

        <!-- Actions -->
        <div class="mt-4 flex flex-wrap gap-2 border-t border-dark-border pt-4">
          <router-link
            v-if="canEdit"
            :to="`/requests/${req.id}/edit`"
            class="rounded-lg border border-dark-border bg-dark-card px-3 py-1.5 text-sm text-dark-muted hover:bg-slate-800/50"
          >
            แก้ไข
          </router-link>
          <button
            v-if="canSubmit"
            @click="handleSubmit"
            class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm text-white hover:bg-primary-500"
          >
            ส่งอนุมัติ
          </button>
          <button
            v-if="showApproveReject && !rejectMode"
            @click="handleApprove"
            class="rounded-lg bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700"
          >
            อนุมัติ
          </button>
          <button
            v-if="showApproveReject && !rejectMode"
            @click="rejectMode = true"
            class="rounded-lg bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700"
          >
            ปฏิเสธ
          </button>
        </div>

        <!-- Reject form -->
        <div v-if="rejectMode" class="mt-4 rounded border border-red-500/30 bg-red-500/10 p-4">
          <label class="mb-1 block text-sm font-medium text-red-400">เหตุผลการปฏิเสธ *</label>
          <textarea
            v-model="rejectNote"
            class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-red-500 focus:outline-none"
            rows="3"
            placeholder="ระบุเหตุผล..."
          ></textarea>
          <div class="mt-2 flex gap-2">
            <button @click="handleReject" class="rounded bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
              ยืนยันปฏิเสธ
            </button>
            <button @click="rejectMode = false" class="rounded border border-dark-border px-3 py-1.5 text-sm text-dark-muted hover:bg-slate-800">
              ยกเลิก
            </button>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div v-if="req.items && req.items.length > 0" class="mb-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <h3 class="mb-3 text-sm font-semibold text-dark-muted">รายการงบประมาณ</h3>
        <table class="min-w-full divide-y divide-dark-border">
          <thead class="bg-dark-bg">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-dark-muted">ชื่อรายการ</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-dark-muted">จำนวน</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-dark-muted">ราคาหน่วย</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-dark-muted">จำนวนเงิน</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-dark-muted">หมายเหตุ</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-border">
            <tr v-for="item in req.items" :key="item.id">
              <td class="px-3 py-2 text-sm">{{ item.item_name }}</td>
              <td class="px-3 py-2 text-right text-sm">{{ parseFloat(item.quantity).toLocaleString() }}</td>
              <td class="px-3 py-2 text-right text-sm">{{ parseFloat(item.unit_price).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}</td>
              <td class="px-3 py-2 text-right text-sm font-medium">{{ parseFloat(item.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}</td>
              <td class="px-3 py-2 text-sm text-dark-muted">{{ item.remark || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Approval history -->
      <div v-if="req.approvals && req.approvals.length > 0" class="mb-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <h3 class="mb-3 text-sm font-semibold text-dark-muted">ประวัติการดำเนินการ</h3>
        <div class="space-y-3">
          <div
            v-for="log in req.approvals"
            :key="log.id"
            class="flex items-start gap-3 border-l-2 border-dark-border py-2 pl-4"
          >
            <div>
              <p class="text-sm">
                <span class="font-medium text-white">{{ log.user_name || 'ผู้ใช้' }}</span>
                <span class="mx-1 text-dark-muted">—</span>
                <span class="text-dark-muted">{{ actionLabels[log.action] || log.action }}</span>
              </p>
              <p v-if="log.note" class="text-sm text-dark-muted">{{ log.note }}</p>
              <p class="text-xs text-dark-muted">{{ formatDate(log.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- File attachments -->
      <div class="mb-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <FileUploader :request-id="req.id" :disabled="false" />
      </div>
    </template>
  </div>
</template>

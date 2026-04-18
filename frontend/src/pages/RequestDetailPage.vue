<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/StatusBadge.vue'
import FileUploader from '@/components/FileUploader.vue'

const route = useRoute()
const router = useRouter()
const store = useBudgetRequestStore()
const auth = useAuthStore()

const rejectMode = ref(false)
const rejectNote = ref('')
const errorMsg = ref('')

const req = computed(() => store.currentRequest)
const isOwner = computed(() => req.value && auth.user && req.value.created_by === auth.user.id)
const isAdmin = computed(() => auth.user?.role === 'admin')
const canEdit = computed(() => isOwner.value && (req.value?.request_status === 'draft' || req.value?.request_status === 'saved'))
const canSubmit = computed(() => isOwner.value && (req.value?.request_status === 'draft' || req.value?.request_status === 'saved'))
const canApprove = computed(() => req.value?.request_status === 'pending')
const showApproveReject = computed(() => canApprove.value && (isAdmin.value || !isOwner.value))

onMounted(() => {
  const id = Number(route.params.id)
  if (id) store.fetchById(id)
})

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
  const result = await store.submit(req.value!.id)
  if (!result.ok) errorMsg.value = result.error ?? 'ไม่สามารถส่งอนุมัติได้'
}

async function handleApprove() {
  errorMsg.value = ''
  const result = await store.approve(req.value!.id)
  if (!result.ok) errorMsg.value = result.error ?? 'ไม่สามารถอนุมัติได้'
}

async function handleReject() {
  if (!rejectNote.value.trim()) {
    errorMsg.value = 'กรุณาระบุเหตุผลการปฏิเสธ'
    return
  }
  errorMsg.value = ''
  const result = await store.reject(req.value!.id, rejectNote.value.trim())
  if (!result.ok) {
    errorMsg.value = result.error ?? 'ไม่สามารถปฏิเสธได้'
  } else {
    rejectMode.value = false
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">รายละเอียดคำขอ</h1>
      <router-link to="/requests" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; กลับ
      </router-link>
    </div>

    <div v-if="store.loading" class="py-16 text-center text-gray-500">กำลังโหลด...</div>

    <div v-else-if="!req" class="rounded-lg bg-white py-16 text-center shadow">
      <p class="text-gray-500">ไม่พบคำของบประมาณ</p>
    </div>

    <template v-else>
      <div v-if="errorMsg" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700" role="alert">
        {{ errorMsg }}
      </div>

      <!-- Request info -->
      <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ req.request_title }}</h2>
            <p class="mt-1 text-sm text-gray-500">
              ปีงบ {{ req.fiscal_year }}
              <span v-if="req.org_name"> · {{ req.org_name }}</span>
            </p>
          </div>
          <StatusBadge :status="req.request_status" />
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
          <div>
            <span class="text-gray-500">ยอดรวม</span>
            <p class="font-semibold text-gray-900">{{ formatAmount(req.total_amount) }} บาท</p>
          </div>
          <div>
            <span class="text-gray-500">ผู้สร้าง</span>
            <p class="text-gray-900">{{ req.created_by_name || '-' }}</p>
          </div>
          <div>
            <span class="text-gray-500">วันที่สร้าง</span>
            <p class="text-gray-900">{{ formatDate(req.created_at) }}</p>
          </div>
          <div>
            <span class="text-gray-500">วันที่ส่ง</span>
            <p class="text-gray-900">{{ formatDate(req.submitted_at) }}</p>
          </div>
        </div>

        <div v-if="req.rejected_reason" class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
          <strong>เหตุผลการปฏิเสธ:</strong> {{ req.rejected_reason }}
        </div>

        <!-- Actions -->
        <div class="mt-4 flex flex-wrap gap-2 border-t pt-4">
          <router-link
            v-if="canEdit"
            :to="`/requests/${req.id}/edit`"
            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
          >
            แก้ไข
          </router-link>
          <button
            v-if="canSubmit"
            @click="handleSubmit"
            class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700"
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
        <div v-if="rejectMode" class="mt-4 rounded border border-red-200 bg-red-50 p-4">
          <label class="mb-1 block text-sm font-medium text-red-800">เหตุผลการปฏิเสธ *</label>
          <textarea
            v-model="rejectNote"
            class="w-full rounded border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none"
            rows="3"
            placeholder="ระบุเหตุผล..."
          ></textarea>
          <div class="mt-2 flex gap-2">
            <button @click="handleReject" class="rounded bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700">
              ยืนยันปฏิเสธ
            </button>
            <button @click="rejectMode = false" class="rounded border px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100">
              ยกเลิก
            </button>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div v-if="req.items && req.items.length > 0" class="mb-6 rounded-lg bg-white p-6 shadow">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">รายการงบประมาณ</h3>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ชื่อรายการ</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">จำนวน</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">ราคาหน่วย</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">จำนวนเงิน</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">หมายเหตุ</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in req.items" :key="item.id">
              <td class="px-3 py-2 text-sm">{{ item.item_name }}</td>
              <td class="px-3 py-2 text-right text-sm">{{ parseFloat(item.quantity).toLocaleString() }}</td>
              <td class="px-3 py-2 text-right text-sm">{{ parseFloat(item.unit_price).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}</td>
              <td class="px-3 py-2 text-right text-sm font-medium">{{ parseFloat(item.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}</td>
              <td class="px-3 py-2 text-sm text-gray-500">{{ item.remark || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Approval history -->
      <div v-if="req.approvals && req.approvals.length > 0" class="mb-6 rounded-lg bg-white p-6 shadow">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">ประวัติการดำเนินการ</h3>
        <div class="space-y-3">
          <div
            v-for="log in req.approvals"
            :key="log.id"
            class="flex items-start gap-3 border-l-2 border-gray-200 py-2 pl-4"
          >
            <div>
              <p class="text-sm">
                <span class="font-medium text-gray-900">{{ log.user_name || 'ผู้ใช้' }}</span>
                <span class="mx-1 text-gray-500">—</span>
                <span class="text-gray-700">{{ actionLabels[log.action] || log.action }}</span>
              </p>
              <p v-if="log.note" class="text-sm text-gray-500">{{ log.note }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(log.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- File attachments -->
      <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <FileUploader :request-id="req.id" :disabled="false" />
      </div>
    </template>
  </div>
</template>

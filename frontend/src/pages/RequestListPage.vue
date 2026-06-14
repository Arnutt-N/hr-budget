<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import StatusBadge from '@/components/StatusBadge.vue'
import type { RequestStatus } from '@/types/budget-request'

const router = useRouter()
const store = useBudgetRequestStore()
const { data: fiscalYears } = useFiscalYearList()

const filterStatus = ref<RequestStatus | ''>('')
const filterFiscalYear = ref<string>('')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const filterSearch = ref('')

onMounted(() => {
  store.fetchList()
})

function applyFilters() {
  store.fetchList({
    status: filterStatus.value || undefined,
    fiscal_year: filterFiscalYear.value ? Number(filterFiscalYear.value) : undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
    search: filterSearch.value || undefined,
    page: 1,
  })
}

function goToPage(page: number) {
  store.fetchList({
    status: filterStatus.value || undefined,
    fiscal_year: filterFiscalYear.value ? Number(filterFiscalYear.value) : undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
    search: filterSearch.value || undefined,
    page,
  })
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatAmount(amount: string | null): string {
  if (!amount) return '-'
  return parseFloat(amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">คำของบประมาณ</h1>
      <router-link
        to="/requests/create"
        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500"
      >
        + สร้างคำขอใหม่
      </router-link>
    </div>

    <!-- Filters -->
    <div class="mb-4 rounded-lg bg-dark-card border border-dark-border p-4 shadow">
      <div class="flex flex-wrap gap-3">
        <select
          v-model="filterFiscalYear"
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
        >
          <option value="">ทุกปีงบ</option>
          <option v-for="fy in fiscalYears ?? []" :key="fy.id" :value="fy.year">
            {{ fy.year }}{{ fy.is_current ? ' (ปีปัจจุบัน)' : '' }}
          </option>
        </select>
        <select
          v-model="filterStatus"
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
        >
          <option value="">ทุกสถานะ</option>
          <option value="draft">ร่าง</option>
          <option value="saved">บันทึกแล้ว</option>
          <option value="pending">รออนุมัติ</option>
          <option value="approved">อนุมัติแล้ว</option>
          <option value="rejected">ปฏิเสธ</option>
        </select>
        <input
          v-model="filterDateFrom"
          type="date"
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
          title="จากวันที่"
        />
        <input
          v-model="filterDateTo"
          type="date"
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
          title="ถึงวันที่"
        />
        <input
          v-model="filterSearch"
          type="text"
          placeholder="ค้นหาชื่อคำขอ..."
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
          @keyup.enter="applyFilters"
        />
        <button
          @click="applyFilters"
          class="rounded bg-gray-700 px-3 py-1.5 text-sm text-white hover:bg-gray-800"
        >
          ค้นหา
        </button>
      </div>
    </div>

    <!-- Error -->
    <div v-if="store.error" class="mb-4 rounded bg-red-500/10 p-3 text-sm text-red-400" role="alert">
      {{ store.error }}
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="py-12 text-center text-dark-muted">กำลังโหลด...</div>

    <!-- Table -->
    <div v-else-if="store.requests.length > 0" class="overflow-x-auto rounded-lg bg-dark-card border border-dark-border shadow">
      <table class="min-w-full divide-y divide-dark-border">
        <thead class="bg-dark-bg">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">ชื่อคำขอ</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">สถานะ</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-dark-muted">ยอดรวม</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">ผู้สร้าง</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">วันที่</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-dark-muted">จัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-dark-border">
          <tr v-for="req in store.requests" :key="req.id" class="hover:bg-slate-800/50">
            <td class="px-4 py-3 text-sm">
              <router-link :to="`/requests/${req.id}`" class="text-primary-400 hover:text-primary-500 hover:underline">
                {{ req.request_title }}
              </router-link>
            </td>
            <td class="px-4 py-3">
              <StatusBadge :status="req.request_status" />
            </td>
            <td class="px-4 py-3 text-right text-sm">{{ formatAmount(req.total_amount) }}</td>
            <td class="px-4 py-3 text-sm text-dark-muted">{{ req.created_by_name || '-' }}</td>
            <td class="px-4 py-3 text-sm text-dark-muted">{{ formatDate(req.created_at) }}</td>
            <td class="px-4 py-3 text-center">
              <router-link
                :to="`/requests/${req.id}`"
                class="text-primary-400 hover:text-primary-500 hover:underline text-sm"
              >
                ดู
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="rounded-lg bg-dark-card border border-dark-border py-16 text-center shadow">
      <p class="text-dark-muted">ไม่มีคำของบประมาณ</p>
      <router-link
        to="/requests/create"
        class="mt-4 inline-block text-primary-400 hover:text-primary-500 hover:underline text-sm"
      >
        สร้างคำขอใหม่
      </router-link>
    </div>

    <!-- Pagination -->
    <div v-if="store.meta && store.meta.total_pages > 1" class="mt-4 flex items-center justify-center gap-2">
      <button
        :disabled="store.currentPage <= 1"
        @click="goToPage(store.currentPage - 1)"
        class="rounded border border-dark-border text-dark-muted px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-dark-muted">
        หน้า {{ store.currentPage }} / {{ store.totalPages }}
      </span>
      <button
        :disabled="store.currentPage >= store.totalPages"
        @click="goToPage(store.currentPage + 1)"
        class="rounded border border-dark-border text-dark-muted px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

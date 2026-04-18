<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'
import { useFiscalYearStore } from '@/stores/fiscalYears'
import StatusBadge from '@/components/StatusBadge.vue'
import type { RequestStatus } from '@/types/budget-request'

const router = useRouter()
const store = useBudgetRequestStore()
const fyStore = useFiscalYearStore()

const filterStatus = ref<RequestStatus | ''>('')
const filterFiscalYear = ref<string>('')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const filterSearch = ref('')

onMounted(() => {
  fyStore.fetchList()
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
      <h1 class="text-2xl font-bold text-gray-900">คำของบประมาณ</h1>
      <router-link
        to="/requests/create"
        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
      >
        + สร้างคำขอใหม่
      </router-link>
    </div>

    <!-- Filters -->
    <div class="mb-4 rounded-lg bg-white p-4 shadow">
      <div class="flex flex-wrap gap-3">
        <select
          v-model="filterFiscalYear"
          class="rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
        >
          <option value="">ทุกปีงบ</option>
          <option v-for="fy in fyStore.fiscalYears" :key="fy.id" :value="fy.year">
            {{ fy.year }}{{ fy.is_current ? ' (ปีปัจจุบัน)' : '' }}
          </option>
        </select>
        <select
          v-model="filterStatus"
          class="rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
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
          class="rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
          title="จากวันที่"
        />
        <input
          v-model="filterDateTo"
          type="date"
          class="rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
          title="ถึงวันที่"
        />
        <input
          v-model="filterSearch"
          type="text"
          placeholder="ค้นหาชื่อคำขอ..."
          class="rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
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
    <div v-if="store.error" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700" role="alert">
      {{ store.error }}
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="py-12 text-center text-gray-500">กำลังโหลด...</div>

    <!-- Table -->
    <div v-else-if="store.requests.length > 0" class="overflow-x-auto rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">ชื่อคำขอ</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">สถานะ</th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">ยอดรวม</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">ผู้สร้าง</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">วันที่</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">จัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="req in store.requests" :key="req.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm">
              <router-link :to="`/requests/${req.id}`" class="text-blue-600 hover:underline">
                {{ req.request_title }}
              </router-link>
            </td>
            <td class="px-4 py-3">
              <StatusBadge :status="req.request_status" />
            </td>
            <td class="px-4 py-3 text-right text-sm">{{ formatAmount(req.total_amount) }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ req.created_by_name || '-' }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(req.created_at) }}</td>
            <td class="px-4 py-3 text-center">
              <router-link
                :to="`/requests/${req.id}`"
                class="text-blue-600 hover:underline text-sm"
              >
                ดู
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="rounded-lg bg-white py-16 text-center shadow">
      <p class="text-gray-500">ไม่มีคำของบประมาณ</p>
      <router-link
        to="/requests/create"
        class="mt-4 inline-block text-blue-600 hover:underline text-sm"
      >
        สร้างคำขอใหม่
      </router-link>
    </div>

    <!-- Pagination -->
    <div v-if="store.meta && store.meta.total_pages > 1" class="mt-4 flex items-center justify-center gap-2">
      <button
        :disabled="store.currentPage <= 1"
        @click="goToPage(store.currentPage - 1)"
        class="rounded border px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-gray-600">
        หน้า {{ store.currentPage }} / {{ store.totalPages }}
      </span>
      <button
        :disabled="store.currentPage >= store.totalPages"
        @click="goToPage(store.currentPage + 1)"
        class="rounded border px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

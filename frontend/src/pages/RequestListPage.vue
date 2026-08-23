<script setup lang="ts">
import { ref, computed } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { useBudgetRequestList } from '@/queries/useBudgetRequests'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import type { ListFilters, RequestStatus } from '@/types/budget-request'

const { data: fiscalYears } = useFiscalYearList()

const PER_PAGE = 20

const STATUS_OPTIONS: { label: string; value: RequestStatus }[] = [
  { label: 'ร่าง', value: 'draft' },
  { label: 'บันทึกแล้ว', value: 'saved' },
  { label: 'รออนุมัติ', value: 'pending' },
  { label: 'อนุมัติแล้ว', value: 'approved' },
  { label: 'ปฏิเสธ', value: 'rejected' },
]

const fiscalYearOptions = computed(() =>
  (fiscalYears.value ?? []).map((fy) => ({
    label: `${fy.year}${fy.is_current ? ' (ปีปัจจุบัน)' : ''}`,
    value: fy.year,
  })),
)

// `filters` is the applied query input; editing the inputs is staged until "ค้นหา".
const filters = ref<ListFilters>({ page: 1, per_page: PER_PAGE })
const filterStatus = ref<RequestStatus | ''>('')
const filterFiscalYear = ref<number | ''>('')
const filterSearch = ref('')

const query = useBudgetRequestList(filters)
const requests = computed(() => query.data.value?.data ?? [])
const meta = computed(() => query.data.value?.meta ?? null)
const currentPage = computed(() => meta.value?.page ?? 1)
const totalRecords = computed(() => meta.value?.total ?? 0)

function applyFilters() {
  filters.value = {
    status: filterStatus.value || undefined,
    fiscal_year: filterFiscalYear.value === '' ? undefined : Number(filterFiscalYear.value),
    search: filterSearch.value || undefined,
    page: 1,
    per_page: PER_PAGE,
  }
}

function onPage(event: { page: number }) {
  // PrimeVue pages are 0-based; the API contract is 1-based.
  filters.value = { ...filters.value, page: event.page + 1 }
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
    <PageHeader title="คำของบประมาณ">
      <Button label="สร้างคำขอใหม่" icon="pi pi-plus" @click="$router.push('/requests/create')" />
    </PageHeader>

    <!-- Filters -->
    <div class="mb-4 rounded-lg bg-dark-card border border-dark-border p-4 shadow">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-col gap-1">
          <label id="req-filter-fy-label" class="text-xs text-dark-muted">ปีงบประมาณ</label>
          <Select
            v-model="filterFiscalYear"
            :options="fiscalYearOptions"
            option-label="label"
            option-value="value"
            aria-labelledby="req-filter-fy-label"
            placeholder="ทุกปีงบ"
            show-clear
            class="w-44"
          />
        </div>
        <div class="flex flex-col gap-1">
          <label id="req-filter-status-label" class="text-xs text-dark-muted">สถานะ</label>
          <Select
            v-model="filterStatus"
            :options="STATUS_OPTIONS"
            option-label="label"
            option-value="value"
            aria-labelledby="req-filter-status-label"
            placeholder="ทุกสถานะ"
            show-clear
            class="w-44"
          />
        </div>
        <div class="flex flex-col gap-1">
          <label for="req-filter-search" class="text-xs text-dark-muted">ค้นหา</label>
          <InputText
            id="req-filter-search"
            v-model="filterSearch"
            placeholder="ค้นหาชื่อคำขอ..."
            class="w-64"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-xs text-dark-muted">&nbsp;</span>
          <Button label="ค้นหา" severity="secondary" @click="applyFilters" />
        </div>
      </div>
    </div>

    <QueryErrorState v-if="query.isError.value" :error="query.error.value" />

    <DataTable
      v-else
      :value="requests"
      :lazy="true"
      :loading="query.isLoading.value"
      paginator
      :rows="PER_PAGE"
      :total-records="totalRecords"
      :first="(currentPage - 1) * PER_PAGE"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
      @page="onPage"
    >
      <template #empty>
        <ListEmptyState message="ไม่มีคำของบประมาณ">
          <router-link
            to="/requests/create"
            class="mt-4 inline-block text-primary-400 hover:text-primary-500 hover:underline text-sm"
          >
            สร้างคำขอใหม่
          </router-link>
        </ListEmptyState>
      </template>

      <Column header="ชื่อคำขอ">
        <template #body="{ data }">
          <router-link
            :to="`/requests/${data.id}`"
            class="text-primary-400 hover:text-primary-500 hover:underline"
          >
            {{ data.request_title }}
          </router-link>
        </template>
      </Column>
      <Column header="สถานะ">
        <template #body="{ data }">
          <StatusBadge :status="data.request_status" />
        </template>
      </Column>
      <Column header="ยอดรวม" class="text-right">
        <template #body="{ data }">
          <span class="text-sm">{{ formatAmount(data.total_amount) }}</span>
        </template>
      </Column>
      <Column header="ผู้สร้าง">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ data.created_by_name || '-' }}</span>
        </template>
      </Column>
      <Column header="วันที่">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ formatDate(data.created_at) }}</span>
        </template>
      </Column>
      <Column header="จัดการ" class="text-center">
        <template #body="{ data }">
          <router-link
            :to="`/requests/${data.id}`"
            class="text-primary-400 hover:text-primary-500 hover:underline text-sm"
          >
            ดู
          </router-link>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

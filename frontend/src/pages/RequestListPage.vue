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
import { useFiscalYearOptions } from '@/queries/useFiscalYears'
import { formatThaiDate } from '@/lib/date'
import { formatBaht } from '@/lib/format'
import { STATUS_LABELS } from '@/types/budget-request'
import type { ListFilters, RequestStatus } from '@/types/budget-request'

const PER_PAGE = 20

// 'confirmed' is not offered as a list filter (transient state before approval)
const STATUS_OPTIONS: { label: string; value: RequestStatus }[] = (
  ['draft', 'saved', 'pending', 'approved', 'rejected'] as const
).map((value) => ({ label: STATUS_LABELS[value], value }))

const fiscalYearOptions = useFiscalYearOptions()

// `filters` is the applied query input; editing the inputs is staged until "ค้นหา".
const filters = ref<ListFilters>({ page: 1, per_page: PER_PAGE })
// nullable: PrimeVue Select emits null on clear
const filterStatus = ref<RequestStatus | '' | null>('')
// PrimeVue Select emits null on clear (not ''), so the empty state is `| null`.
const filterFiscalYear = ref<number | null>(null)
const filterSearch = ref('')

const query = useBudgetRequestList(filters)
const requests = computed(() => query.data.value?.data ?? [])
const meta = computed(() => query.data.value?.meta ?? null)
const currentPage = computed(() => meta.value?.page ?? 1)
const totalRecords = computed(() => meta.value?.total ?? 0)

function applyFilters() {
  filters.value = {
    status: filterStatus.value || undefined,
    fiscal_year: filterFiscalYear.value === null ? undefined : Number(filterFiscalYear.value),
    search: filterSearch.value || undefined,
    page: 1,
    per_page: PER_PAGE,
  }
}

function onPage(event: { page: number }) {
  // PrimeVue pages are 0-based; the API contract is 1-based.
  filters.value = { ...filters.value, page: event.page + 1 }
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

    <div class="table-scroll" v-else>
    <DataTable
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
          <span class="text-sm">{{ formatBaht(data.total_amount) }}</span>
        </template>
      </Column>
      <Column header="ผู้สร้าง">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ data.created_by_name || '-' }}</span>
        </template>
      </Column>
      <Column header="วันที่">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ formatThaiDate(data.created_at) }}</span>
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
  </div>
</template>

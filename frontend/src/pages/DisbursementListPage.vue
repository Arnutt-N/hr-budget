<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Select from 'primevue/select'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDisbursementSessions, useDeleteSession } from '@/queries/useDisbursements'
import { useFiscalYearOptions } from '@/queries/useFiscalYears'
import { formatThaiDate } from '@/lib/date'
import { useDisbursementWizard } from '@/stores/disbursementWizard'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { MONTH_LABELS, MONTH_OPTIONS, type SessionFilters } from '@/types/disbursement'

const router = useRouter()
const confirmDelete = useDeleteConfirm()
const toast = useToast()
const wizard = useDisbursementWizard()

const PER_PAGE = 20
const filters = ref<SessionFilters>({ page: 1, per_page: PER_PAGE })
// PrimeVue Select emits null on clear (not ''), so the empty state is `| null`.
const filterFiscalYear = ref<number | null>(null)
const filterMonth = ref<number | null>(null)

const query = useDisbursementSessions(filters)
const sessions = computed(() => query.data.value?.data ?? [])
const meta = computed(() => query.data.value?.meta ?? null)
const currentPage = computed(() => meta.value?.page ?? 1)
const totalRecords = computed(() => meta.value?.total ?? 0)

const deleteMut = useDeleteSession()

const fiscalYearOptions = useFiscalYearOptions()

function applyFilters(): void {
  filters.value = {
    fiscal_year: filterFiscalYear.value === null ? undefined : Number(filterFiscalYear.value),
    record_month: filterMonth.value === null ? undefined : Number(filterMonth.value),
    page: 1,
    per_page: PER_PAGE,
  }
}

function onPage(event: { page: number }): void {
  // PrimeVue pages are 0-based; the API contract is 1-based.
  filters.value = { ...filters.value, page: event.page + 1 }
}

function startWizard(): void {
  wizard.reset()
  router.push('/disbursements/wizard')
}

function confirmDeleteSession(id: number, orgName: string): void {
  confirmDelete({
    message: `ยืนยันลบรอบการบันทึกของ "${orgName}"? ข้อมูลการเบิกจ่ายทั้งหมดในรอบนี้จะถูกลบ`,
    accept: async () => {
      try {
        await deleteMut.mutateAsync(id)
        toast.add({ severity: 'success', summary: 'ลบรอบการบันทึกสำเร็จ', life: 3000 })
      } catch (e) {
        toast.add({
          severity: 'error',
          summary: 'ลบไม่สำเร็จ',
          detail: e instanceof Error ? e.message : 'เกิดข้อผิดพลาด',
          life: 5000,
        })
      }
    },
  })
}
</script>

<template>
  <div>
    <PageHeader title="บันทึกการเบิกจ่ายงบประมาณ">
      <Button label="บันทึกการเบิกจ่าย" icon="pi pi-plus" @click="startWizard" />
    </PageHeader>

    <!-- Filters -->
    <div class="mb-4 rounded-lg bg-dark-card border border-dark-border p-4 shadow">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-col gap-1">
          <label id="disb-filter-fy-label" class="text-xs text-dark-muted">ปีงบประมาณ</label>
          <Select
            v-model="filterFiscalYear"
            :options="fiscalYearOptions"
            option-label="label"
            option-value="value"
            aria-labelledby="disb-filter-fy-label"
            placeholder="ทุกปีงบ"
            show-clear
            class="w-44"
          />
        </div>
        <div class="flex flex-col gap-1">
          <label id="disb-filter-month-label" class="text-xs text-dark-muted">เดือน</label>
          <Select
            v-model="filterMonth"
            :options="MONTH_OPTIONS"
            option-label="label"
            option-value="value"
            aria-labelledby="disb-filter-month-label"
            placeholder="ทุกเดือน"
            show-clear
            class="w-44"
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
      :value="sessions"
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
        <ListEmptyState message="ยังไม่มีรอบการบันทึกการเบิกจ่าย">
          <button
            type="button"
            class="mt-4 inline-block text-sm text-primary-400 hover:text-primary-500 hover:underline"
            @click="startWizard"
          >
            เริ่มบันทึกการเบิกจ่าย
          </button>
        </ListEmptyState>
      </template>

      <Column header="หน่วยงาน">
        <template #body="{ data }">
          <span class="text-sm text-dark-text">{{ data.org_name || `#${data.organization_id}` }}</span>
        </template>
      </Column>
      <Column header="ปีงบ">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ data.fiscal_year }}</span>
        </template>
      </Column>
      <Column header="เดือน">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ MONTH_LABELS[data.record_month] ?? data.record_month }}</span>
        </template>
      </Column>
      <Column header="วันที่บันทึก">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ formatThaiDate(data.record_date) }}</span>
        </template>
      </Column>
      <Column header="จัดการ" class="text-center">
        <template #body="{ data }">
          <Button
            label="ลบ"
            size="small"
            text
            severity="danger"
            @click="confirmDeleteSession(data.id, data.org_name || `#${data.organization_id}`)"
          />
        </template>
      </Column>
    </DataTable>
    </div>
  </div>
</template>

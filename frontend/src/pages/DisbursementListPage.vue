<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { useDisbursementSessions, useDeleteSession } from '@/queries/useDisbursements'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import { useDisbursementWizard } from '@/stores/disbursementWizard'
import { MONTH_LABELS, type SessionFilters } from '@/types/disbursement'

const router = useRouter()
const confirm = useConfirm()
const toast = useToast()
const wizard = useDisbursementWizard()

const { data: fiscalYears } = useFiscalYearList()

const PER_PAGE = 20
const filters = ref<SessionFilters>({ page: 1, per_page: PER_PAGE })
const filterFiscalYear = ref<string>('')
const filterMonth = ref<string>('')

const query = useDisbursementSessions(filters)
const sessions = computed(() => query.data.value?.data ?? [])
const meta = computed(() => query.data.value?.meta ?? null)
const currentPage = computed(() => meta.value?.page ?? 1)
const totalPages = computed(() => meta.value?.total_pages ?? 0)

const deleteMut = useDeleteSession()

const monthOptions = computed(() =>
  Object.entries(MONTH_LABELS).map(([value, label]) => ({ value: Number(value), label })),
)

function applyFilters(): void {
  filters.value = {
    fiscal_year: filterFiscalYear.value ? Number(filterFiscalYear.value) : undefined,
    record_month: filterMonth.value ? Number(filterMonth.value) : undefined,
    page: 1,
    per_page: PER_PAGE,
  }
}

function goToPage(page: number): void {
  filters.value = { ...filters.value, page }
}

function startWizard(): void {
  wizard.reset()
  router.push('/disbursements/wizard')
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function confirmDelete(id: number, orgName: string): void {
  confirm.require({
    message: `ยืนยันลบรอบการบันทึกของ "${orgName}"? ข้อมูลการเบิกจ่ายทั้งหมดในรอบนี้จะถูกลบ`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMut.mutateAsync(id)
        toast.add({ severity: 'success', summary: 'ลบรอบการบันทึกสำเร็จ', life: 3000 })
      } catch (e: unknown) {
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
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">บันทึกการเบิกจ่ายงบประมาณ</h1>
      <button
        type="button"
        @click="startWizard"
        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500"
      >
        + บันทึกการเบิกจ่าย
      </button>
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
          v-model="filterMonth"
          class="rounded bg-dark-card border border-dark-border text-dark-text px-3 py-1.5 text-sm focus:border-primary-500 focus:outline-none"
        >
          <option value="">ทุกเดือน</option>
          <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
        <button
          @click="applyFilters"
          class="rounded bg-gray-700 px-3 py-1.5 text-sm text-white hover:bg-gray-800"
        >
          ค้นหา
        </button>
      </div>
    </div>

    <!-- Error -->
    <div
      v-if="query.isError.value"
      class="mb-4 rounded bg-red-500/10 p-3 text-sm text-red-400"
      role="alert"
    >
      {{ (query.error.value as Error | null)?.message ?? 'เกิดข้อผิดพลาด' }}
    </div>

    <!-- Loading -->
    <div v-if="query.isLoading.value" class="py-12 text-center text-dark-muted">กำลังโหลด...</div>

    <!-- Table -->
    <div
      v-else-if="sessions.length > 0"
      class="overflow-x-auto rounded-lg bg-dark-card border border-dark-border shadow"
    >
      <table class="min-w-full divide-y divide-dark-border">
        <thead class="bg-dark-bg">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">หน่วยงาน</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">ปีงบ</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">เดือน</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-dark-muted">วันที่บันทึก</th>
            <th class="px-4 py-3 text-center text-xs font-medium text-dark-muted">จัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-dark-border">
          <tr v-for="s in sessions" :key="s.id" class="hover:bg-slate-800/50">
            <td class="px-4 py-3 text-sm text-dark-text">{{ s.org_name || `#${s.organization_id}` }}</td>
            <td class="px-4 py-3 text-sm text-dark-muted">{{ s.fiscal_year }}</td>
            <td class="px-4 py-3 text-sm text-dark-muted">
              {{ MONTH_LABELS[s.record_month] ?? s.record_month }}
            </td>
            <td class="px-4 py-3 text-sm text-dark-muted">{{ formatDate(s.record_date) }}</td>
            <td class="px-4 py-3 text-center">
              <button
                type="button"
                @click="confirmDelete(s.id, s.org_name || `#${s.organization_id}`)"
                class="text-sm text-red-400 hover:text-red-300"
              >
                ลบ
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="rounded-lg bg-dark-card border border-dark-border py-16 text-center shadow">
      <p class="text-dark-muted">ยังไม่มีรอบการบันทึกการเบิกจ่าย</p>
      <button
        type="button"
        @click="startWizard"
        class="mt-4 inline-block text-sm text-primary-400 hover:text-primary-500 hover:underline"
      >
        เริ่มบันทึกการเบิกจ่าย
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-4 flex items-center justify-center gap-2">
      <button
        :disabled="currentPage <= 1"
        @click="goToPage(currentPage - 1)"
        class="rounded border border-dark-border text-dark-muted px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-dark-muted">หน้า {{ currentPage }} / {{ totalPages }}</span>
      <button
        :disabled="currentPage >= totalPages"
        @click="goToPage(currentPage + 1)"
        class="rounded border border-dark-border text-dark-muted px-3 py-1.5 text-sm disabled:opacity-40"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

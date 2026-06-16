<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Landmark, Banknote, Wallet, Percent, Inbox, Download } from '@lucide/vue'
import Select from 'primevue/select'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import StatCard from '@/components/StatCard.vue'
import HorizontalBarChart from '@/components/HorizontalBarChart.vue'
import { useExecutionReport, useExecutionYears } from '@/queries/useBudgetExecution'
import { executionExportUrl } from '@/api/budgetExecution'
import { useOrganizationList } from '@/queries/useOrganizations'
import type { ExecutionProject } from '@/types/budget-execution'

// Current Buddhist fiscal year (Oct 1 starts the new BE year) — same rule as
// config/app.php and AppLayout, so the page defaults to the live year.
const currentFiscalYear = (() => {
  const now = new Date()
  const beYear = now.getFullYear() + 543
  return now.getMonth() + 1 >= 10 ? beYear + 1 : beYear
})()

const selectedYear = ref<number>(currentFiscalYear)
const selectedOrg = ref<number | null>(null)

const yearsQuery = useExecutionYears()
const orgQuery = useOrganizationList()
const reportQuery = useExecutionReport(selectedYear, selectedOrg)

// Year options: data years ∪ current year, deduped, newest first — so the
// current year is always selectable even before any disbursement is recorded.
const yearOptions = computed(() => {
  const years = new Set<number>([currentFiscalYear])
  for (const y of yearsQuery.data.value ?? []) years.add(y.fiscal_year)
  return [...years].sort((a, b) => b - a).map((y) => ({ label: `ปีงบ ${y}`, value: y }))
})

const orgOptions = computed(() => [
  { label: 'ทุกหน่วยงาน', value: null },
  ...(orgQuery.data.value ?? []).map((o) => ({ label: o.name_th, value: o.id })),
])

const baht = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' })
const num = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

const stats = computed(() => reportQuery.data.value?.stats)
const projects = computed<ExecutionProject[]>(() => reportQuery.data.value?.projects ?? [])
const hasData = computed(() => projects.value.length > 0)

const cards = computed(() => {
  const s = stats.value
  if (!s) return []
  return [
    { label: 'งบสุทธิ', value: baht.format(s.total_budget), accent: 'sky' as const, icon: Landmark },
    {
      label: 'ใช้ไปแล้ว',
      value: baht.format(s.total_used),
      accent: 'emerald' as const,
      icon: Banknote,
      sub: `เบิกจริง ${baht.format(s.disbursed)} · PO ${baht.format(s.po)}`,
    },
    { label: 'คงเหลือ', value: baht.format(s.remaining), accent: 'amber' as const, icon: Wallet },
    { label: 'อัตราการใช้จ่าย', value: `${s.used_percent}%`, accent: 'violet' as const, icon: Percent },
  ]
})

const categoryChart = computed(() => reportQuery.data.value?.category_chart ?? { labels: [], values: [] })
const orgChart = computed(() => reportQuery.data.value?.org_chart ?? { labels: [], values: [] })

const exportUrl = computed(() => executionExportUrl(selectedYear.value, selectedOrg.value))

// Row expansion state — expand a project to reveal its activity rows.
const expandedRows = ref<Record<number, boolean>>({})
// Collapse expansions whenever the underlying report changes.
watch([selectedYear, selectedOrg], () => {
  expandedRows.value = {}
})
</script>

<template>
  <div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white">ผลการเบิกจ่ายงบประมาณ</h1>
        <p class="mt-1 text-sm text-dark-muted">
          สรุปการจัดสรรและเบิกจ่ายตามผลผลิต/โครงการ ประจำปีงบ พ.ศ. {{ selectedYear }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <Select
          v-model="selectedYear"
          :options="yearOptions"
          option-label="label"
          option-value="value"
          class="w-40"
        />
        <Select
          v-model="selectedOrg"
          :options="orgOptions"
          option-label="label"
          option-value="value"
          placeholder="ทุกหน่วยงาน"
          class="w-56"
          :loading="orgQuery.isLoading.value"
        />
        <a :href="hasData ? exportUrl : undefined" :class="{ 'pointer-events-none opacity-50': !hasData }">
          <Button label="ส่งออก Excel" severity="success" :disabled="!hasData">
            <template #icon><Download class="mr-2 h-4 w-4" /></template>
          </Button>
        </a>
      </div>
    </header>

    <div
      v-if="reportQuery.isError.value"
      class="rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"
    >
      โหลดรายงานไม่สำเร็จ — {{ (reportQuery.error.value as Error | null)?.message }}
    </div>

    <!-- KPI cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <template v-if="reportQuery.isLoading.value">
        <div
          v-for="n in 4"
          :key="n"
          class="h-28 animate-pulse rounded-xl border border-dark-border bg-dark-card"
        />
      </template>
      <StatCard v-for="c in cards" v-else :key="c.label" v-bind="c" />
    </div>

    <!-- Empty state -->
    <div
      v-if="!reportQuery.isLoading.value && !hasData"
      class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"
    >
      <Inbox class="h-10 w-10" />
      <p class="text-sm">ยังไม่มีข้อมูลการเบิกจ่ายในปีงบนี้</p>
    </div>

    <template v-else-if="hasData">
      <!-- Charts -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-white">งบจัดสรรตามโครงการ (สูงสุด 5)</h2>
          <HorizontalBarChart :labels="categoryChart.labels" :values="categoryChart.values" />
        </section>
        <section class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm">
          <h2 class="mb-4 text-base font-semibold text-white">งบจัดสรรตามหน่วยงาน</h2>
          <HorizontalBarChart
            :labels="orgChart.labels"
            :values="orgChart.values"
            color="#8b5cf6"
            hover-color="#a78bfa"
          />
        </section>
      </div>

      <!-- Breakdown table (expand a project to see its activities) -->
      <section class="rounded-xl border border-dark-border bg-dark-card p-2 shadow-sm">
        <DataTable
          v-model:expanded-rows="expandedRows"
          :value="projects"
          data-key="project_id"
          class="text-sm"
          responsive-layout="scroll"
        >
          <Column expander style="width: 3rem" />
          <Column field="project_name" header="ผลผลิต/โครงการ">
            <template #body="{ data }">
              <div class="font-medium text-white">{{ data.project_name }}</div>
              <div class="text-xs text-dark-muted">{{ data.org_name }}</div>
            </template>
          </Column>
          <Column header="งบสุทธิ" class="text-right">
            <template #body="{ data }">{{ num.format(data.net_budget) }}</template>
          </Column>
          <Column header="ใช้ไป" class="text-right">
            <template #body="{ data }">{{ num.format(data.total_used) }}</template>
          </Column>
          <Column header="คงเหลือ" class="text-right">
            <template #body="{ data }">{{ num.format(data.balance) }}</template>
          </Column>
          <Column header="% ใช้ไป" class="text-right">
            <template #body="{ data }">
              <span
                class="rounded px-2 py-0.5 text-xs font-medium"
                :class="data.used_percent >= 90
                  ? 'bg-emerald-500/15 text-emerald-400'
                  : data.used_percent >= 50
                    ? 'bg-amber-500/15 text-amber-400'
                    : 'bg-slate-500/15 text-slate-300'"
              >
                {{ data.used_percent }}%
              </span>
            </template>
          </Column>

          <template #expansion="{ data }">
            <div class="bg-dark-bg/60 p-3">
              <DataTable :value="data.activities" class="text-xs" data-key="activity_id">
                <Column field="activity_name" header="กิจกรรม" />
                <Column header="งบสุทธิ" class="text-right">
                  <template #body="{ data: a }">{{ num.format(a.net_budget) }}</template>
                </Column>
                <Column header="เบิกจ่าย" class="text-right">
                  <template #body="{ data: a }">{{ num.format(a.disbursed) }}</template>
                </Column>
                <Column header="PO คงค้าง" class="text-right">
                  <template #body="{ data: a }">{{ num.format(a.po) }}</template>
                </Column>
                <Column header="ใช้ไป" class="text-right">
                  <template #body="{ data: a }">{{ num.format(a.total_used) }}</template>
                </Column>
                <Column header="คงเหลือ" class="text-right">
                  <template #body="{ data: a }">{{ num.format(a.balance) }}</template>
                </Column>
                <Column header="% ใช้ไป" class="text-right">
                  <template #body="{ data: a }">{{ a.used_percent }}%</template>
                </Column>
              </DataTable>
            </div>
          </template>
        </DataTable>
      </section>
    </template>
  </div>
</template>

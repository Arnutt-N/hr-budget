<script setup lang="ts">
import { computed, ref } from 'vue'
import { Inbox, Info } from '@lucide/vue'
import Select from 'primevue/select'
import SelectButton from 'primevue/selectbutton'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import ComparisonChart from '@/components/ComparisonChart.vue'
import ForecastChart from '@/components/ForecastChart.vue'
import RequestApprovalChart from '@/components/RequestApprovalChart.vue'
import { useComparison, useForecast, useRequestVsApproved } from '@/queries/useAnalytics'
import { useExecutionYears } from '@/queries/useBudgetExecution'
import type { AnalyticsScope, ComparisonDimension } from '@/types/analytics'

// Current Buddhist fiscal year (Oct 1 starts the new BE year) — same rule as
// config/app.php / BudgetExecutionPage, so the page defaults to the live year.
const currentFiscalYear = (() => {
  const now = new Date()
  const beYear = now.getFullYear() + 543
  return now.getMonth() + 1 >= 10 ? beYear + 1 : beYear
})()

const selectedYear = ref<number>(currentFiscalYear)
const dimension = ref<ComparisonDimension>('quarter')
const activeTab = ref<'comparison' | 'forecast' | 'request'>('comparison')

const yearsQuery = useExecutionYears()

// Year options: data years ∪ current year, deduped, newest first — current year
// always selectable even before any record exists (mirrors BudgetExecutionPage).
const yearOptions = computed(() => {
  const years = new Set<number>([currentFiscalYear])
  for (const y of yearsQuery.data.value ?? []) years.add(y.fiscal_year)
  return [...years].sort((a, b) => b - a).map((y) => ({ label: `ปีงบ ${y}`, value: y }))
})

const dimensionOptions = [
  { label: 'รายปี', value: 'year' as const },
  { label: 'รายไตรมาส', value: 'quarter' as const },
  { label: 'รายเดือน', value: 'month' as const },
]

// In year view the year selector is meaningless (comparison spans the last ≤5
// years), so grey it out with a hint.
const yearSelectorDisabled = computed(
  () => activeTab.value === 'comparison' && dimension.value === 'year',
)

// Per-tab activation gates — feed `enabled` so each query fires only when its
// tab is visible (lazy first-paint).
const comparisonActive = computed(() => activeTab.value === 'comparison')
const forecastActive = computed(() => activeTab.value === 'forecast')
const requestActive = computed(() => activeTab.value === 'request')

const comparisonQuery = useComparison(selectedYear, dimension, comparisonActive)
const forecastQuery = useForecast(selectedYear, forecastActive)
const requestQuery = useRequestVsApproved(selectedYear, requestActive)

// Comparison chart inputs. Year rows label by BE year; quarter/month carry a
// Thai `label`. Empty state checks rows.length (NOT some(n>0)) so a
// low-disbursement year still renders — the whole point of the comparison.
const comparisonLabels = computed(() =>
  (comparisonQuery.data.value?.rows ?? []).map((r) =>
    r.fiscal_year != null ? `ปีงบ ${r.fiscal_year}` : (r.label ?? ''),
  ),
)
const comparisonBudget = computed(() =>
  (comparisonQuery.data.value?.rows ?? []).map((r) => r.budget),
)
const comparisonDisbursed = computed(() =>
  (comparisonQuery.data.value?.rows ?? []).map((r) => r.disbursed),
)
const hasComparison = computed(() => (comparisonQuery.data.value?.rows ?? []).length > 0)

const forecast = computed(() => forecastQuery.data.value)
const hasForecast = computed(() => (forecast.value?.labels ?? []).length > 0)

const requestReport = computed(() => requestQuery.data.value)

// Any section reporting subtree scope → show the "your-org-only" disclaimer.
const isScoped = computed<boolean>(() => {
  const scopes: (AnalyticsScope | undefined)[] = [
    comparisonQuery.data.value?.scope,
    forecastQuery.data.value?.scope,
    requestQuery.data.value?.scope,
  ]
  return scopes.includes('subtree')
})
</script>

<template>
  <div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white">รายงานวิเคราะห์</h1>
        <p class="mt-1 text-sm text-dark-muted">
          เปรียบเทียบงบจัดสรรกับเบิกจ่าย พยากรณ์เทียบจริง และคำขอเทียบอนุมัติ
        </p>
      </div>

      <div class="flex flex-col items-end gap-1">
        <Select
          v-model="selectedYear"
          :options="yearOptions"
          option-label="label"
          option-value="value"
          class="w-40"
          :disabled="yearSelectorDisabled"
        />
        <p v-if="yearSelectorDisabled" class="text-xs text-dark-muted">
          ตัวเลือกปีไม่มีผลในมุมมองรายปี
        </p>
      </div>
    </header>

    <!-- Org-scope disclaimer (any section reporting subtree scope) -->
    <div
      v-if="isScoped"
      class="flex items-center gap-2 rounded-lg border border-sky-800 bg-sky-950/40 px-4 py-2.5 text-sm text-sky-300"
    >
      <Info class="h-4 w-4 shrink-0" />
      <span>แสดงเฉพาะข้อมูลตามสิทธิ์หน่วยงานของคุณ</span>
    </div>

    <Tabs v-model:value="activeTab">
      <TabList>
        <Tab value="comparison">เปรียบเทียบ</Tab>
        <Tab value="forecast">Forecast vs จริง</Tab>
        <Tab value="request">คำขอ vs อนุมัติ</Tab>
      </TabList>

      <TabPanels>
        <!-- ===== เปรียบเทียบ ===== -->
        <TabPanel value="comparison">
          <div class="space-y-4">
            <SelectButton
              v-model="dimension"
              :options="dimensionOptions"
              option-label="label"
              option-value="value"
              :allow-empty="false"
              aria-label="เลือกมุมมองการเปรียบเทียบ"
            />

            <div
              v-if="comparisonQuery.isError.value"
              class="rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"
            >
              โหลดข้อมูลเปรียบเทียบไม่สำเร็จ —
              {{ (comparisonQuery.error.value as Error | null)?.message }}
            </div>

            <div
              v-else-if="comparisonQuery.isLoading.value"
              class="h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"
            />

            <div
              v-else-if="!hasComparison"
              class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"
            >
              <Inbox class="h-10 w-10" />
              <p class="text-sm">ยังไม่มีข้อมูลสำหรับการเปรียบเทียบ</p>
            </div>

            <section
              v-else
              class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"
            >
              <ComparisonChart
                :labels="comparisonLabels"
                :budget="comparisonBudget"
                :disbursed="comparisonDisbursed"
              />
            </section>
          </div>
        </TabPanel>

        <!-- ===== Forecast vs จริง ===== -->
        <TabPanel value="forecast">
          <div
            v-if="forecastQuery.isError.value"
            class="rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"
          >
            โหลดข้อมูล Forecast ไม่สำเร็จ —
            {{ (forecastQuery.error.value as Error | null)?.message }}
          </div>

          <div
            v-else-if="forecastQuery.isLoading.value"
            class="h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"
          />

          <div
            v-else-if="!hasForecast"
            class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"
          >
            <Inbox class="h-10 w-10" />
            <p class="text-sm">ยังไม่มีข้อมูลพยากรณ์ในปีงบนี้</p>
          </div>

          <section
            v-else-if="forecast"
            class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"
          >
            <ForecastChart
              :labels="forecast.labels"
              :forecast-monthly="forecast.forecast_monthly"
              :actual-monthly="forecast.actual_monthly"
              :forecast-cumulative="forecast.forecast_cumulative"
              :actual-cumulative="forecast.actual_cumulative"
            />
          </section>
        </TabPanel>

        <!-- ===== คำขอ vs อนุมัติ ===== -->
        <TabPanel value="request">
          <div
            v-if="requestQuery.isError.value"
            class="rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"
          >
            โหลดข้อมูลคำขอ vs อนุมัติไม่สำเร็จ —
            {{ (requestQuery.error.value as Error | null)?.message }}
          </div>

          <div
            v-else-if="requestQuery.isLoading.value"
            class="h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"
          />

          <RequestApprovalChart v-else-if="requestReport" :report="requestReport" />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>

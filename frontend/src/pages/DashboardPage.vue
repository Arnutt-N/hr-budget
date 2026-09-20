<script setup lang="ts">
import { computed } from 'vue'
import { Landmark, Banknote, Wallet, Percent, Inbox } from '@lucide/vue'
import StatCard from '@/components/StatCard.vue'
import MonthlyExpenditureChart from '@/components/MonthlyExpenditureChart.vue'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import { useDashboardSummary, useMonthlyChart } from '@/queries/useDashboard'

const summaryQuery = useDashboardSummary()
const chartQuery = useMonthlyChart()

const pageSubtitle = computed(() =>
  summaryQuery.data.value
    ? `สรุปสถานะงบประมาณประจำปี พ.ศ. ${summaryQuery.data.value.fiscal_year}`
    : 'สรุปสถานะงบประมาณประจำปี',
)

const baht = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' })

const cards = computed(() => {
  const s = summaryQuery.data.value
  if (!s) return []
  return [
    { label: 'งบประมาณทั้งหมด', value: baht.format(s.total_budget), accent: 'sky' as const, icon: Landmark },
    {
      label: 'เบิกจ่ายแล้ว',
      value: baht.format(s.total_used),
      accent: 'emerald' as const,
      icon: Banknote,
      sub: `เบิกจริง ${baht.format(s.disbursed)}`,
    },
    { label: 'คงเหลือ', value: baht.format(s.remaining), accent: 'amber' as const, icon: Wallet },
    { label: 'อัตราการใช้จ่าย', value: `${s.used_percent}%`, accent: 'violet' as const, icon: Percent },
  ]
})

const hasChartData = computed(() => (chartQuery.data.value?.data ?? []).some((n) => n > 0))
</script>

<template>
  <div class="space-y-6">
    <!-- The ONE page heading (name contains "Dashboard" for the e2e assertion) -->
    <PageHeader title="ภาพรวมงบประมาณ (Dashboard)" :subtitle="pageSubtitle" />

    <!-- Summary error -->
    <QueryErrorState v-if="summaryQuery.isError.value" :error="summaryQuery.error.value" :retry="() => summaryQuery.refetch()" />

    <!-- Stat cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <template v-if="summaryQuery.isLoading.value">
        <div
          v-for="n in 4"
          :key="n"
          class="h-28 animate-pulse rounded-xl border border-dark-border bg-dark-card"
        />
      </template>
      <StatCard v-for="c in cards" v-else :key="c.label" v-bind="c" />
    </div>

    <!-- Monthly expenditure chart -->
    <section class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-white">เบิกจ่ายรายเดือน</h2>
        <span class="text-xs text-dark-muted">หน่วย: บาท</span>
      </div>

      <div v-if="chartQuery.isLoading.value" class="h-72 animate-pulse rounded-lg bg-dark-bg" />
      <QueryErrorState v-else-if="chartQuery.isError.value" :error="chartQuery.error.value" :retry="() => chartQuery.refetch()" />
      <div
        v-else-if="!hasChartData"
        class="flex h-72 flex-col items-center justify-center gap-2 text-dark-muted"
      >
        <Inbox aria-hidden="true" class="h-10 w-10" />
        <p class="text-sm">ยังไม่มีข้อมูลการเบิกจ่ายในปีงบนี้</p>
      </div>
      <template v-else>
      <MonthlyExpenditureChart
        :labels="chartQuery.data.value!.labels"
        :data="chartQuery.data.value!.data"
      />
      <table class="sr-only">
        <caption>เบิกจ่ายรายเดือน (บาท)</caption>
        <thead><tr><th scope="col">เดือน</th><th scope="col">เบิกจ่าย (บาท)</th></tr></thead>
        <tbody>
          <tr v-for="(m, i) in chartQuery.data.value!.labels" :key="m">
            <th scope="row">{{ m }}</th>
            <td>{{ chartQuery.data.value!.data[i] }}</td>
          </tr>
        </tbody>
      </table>
      </template>
    </section>
  </div>
</template>

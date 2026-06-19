<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { FileText, CheckCircle2, Percent, Inbox } from '@lucide/vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend,
} from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import StatCard from '@/components/StatCard.vue'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'
import type { RequestApprovalReport } from '@/types/analytics'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

interface Props {
  report: RequestApprovalReport
}
const props = defineProps<Props>()

const baht = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' })
const bahtPlain = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 })
// approval_rate is a 0–1 ratio — render as a percent, NOT baht.
const percent = new Intl.NumberFormat('th-TH', { style: 'percent', maximumFractionDigits: 1 })

const reduced = usePrefersReducedMotion()

const cards = computed(() => [
  {
    label: 'ยอดขอ',
    value: baht.format(props.report.requested),
    accent: 'sky' as const,
    icon: FileText,
  },
  {
    label: 'ยอดอนุมัติ',
    value: baht.format(props.report.approved),
    accent: 'emerald' as const,
    icon: CheckCircle2,
  },
  {
    label: 'อัตราอนุมัติ',
    value: percent.format(props.report.approval_rate),
    accent: 'violet' as const,
    icon: Percent,
  },
])

const hasOrgData = computed(() => props.report.by_org.length > 0)

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.report.by_org.map((o) => o.org_name),
  datasets: [
    {
      label: 'ยอดขอ',
      data: props.report.by_org.map((o) => o.requested),
      backgroundColor: '#0ea5e9',
      hoverBackgroundColor: '#38bdf8',
      borderRadius: 6,
      maxBarThickness: 22,
    },
    {
      label: 'ยอดอนุมัติ',
      data: props.report.by_org.map((o) => o.approved),
      backgroundColor: '#10b981',
      hoverBackgroundColor: '#34d399',
      borderRadius: 6,
      maxBarThickness: 22,
    },
  ],
}))

const options = computed<ChartOptions<'bar'>>(() => ({
  indexAxis: 'y', // horizontal grouped bars — long Thai org names read better on y
  responsive: true,
  maintainAspectRatio: false,
  animation: reduced.value ? false : undefined,
  plugins: {
    legend: {
      display: true,
      labels: { color: '#cbd5e1', usePointStyle: true, boxWidth: 8 },
    },
    tooltip: {
      backgroundColor: '#1e293b',
      borderColor: '#334155',
      borderWidth: 1,
      titleColor: '#f1f5f9',
      bodyColor: '#94a3b8',
      padding: 10,
      callbacks: {
        label: (ctx: TooltipItem<'bar'>) =>
          ` ${ctx.dataset.label}: ${bahtPlain.format(Number(ctx.parsed.x))} บาท`,
      },
    },
  },
  scales: {
    x: {
      beginAtZero: true,
      grid: { color: '#334155' },
      border: { color: '#334155' },
      ticks: { color: '#94a3b8', callback: (value) => compact.format(Number(value)) },
    },
    y: {
      grid: { display: false },
      border: { color: '#334155' },
      ticks: { color: '#cbd5e1' },
    },
  },
}))
</script>

<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <StatCard v-for="c in cards" :key="c.label" v-bind="c" />
    </div>

    <section class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm">
      <h2 class="mb-4 text-base font-semibold text-white">คำขอ vs อนุมัติ ตามหน่วยงาน</h2>
      <div
        v-if="!hasOrgData"
        class="flex h-72 flex-col items-center justify-center gap-2 text-dark-muted"
      >
        <Inbox class="h-10 w-10" />
        <p class="text-sm">ยังไม่มีคำขอแยกตามหน่วยงานในปีงบนี้</p>
      </div>
      <div v-else class="h-80">
        <Bar :data="chartData" :options="options" />
      </div>
    </section>
  </div>
</template>

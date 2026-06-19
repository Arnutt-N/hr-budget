<script setup lang="ts">
import { computed, ref } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  LineController,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'

// CRITICAL: existing charts register Bar only. A Line chart needs its own
// controller + elements or the render throws "line is not a registered controller".
ChartJS.register(
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  LineController,
  Filler,
  Tooltip,
  Legend,
)

interface Props {
  labels: string[]
  forecastMonthly: number[]
  actualMonthly: number[]
  forecastCumulative: number[]
  actualCumulative: number[]
}
const props = defineProps<Props>()

// Backend sends both series; the toggle just swaps which arrays feed the chart.
const mode = ref<'monthly' | 'cumulative'>('monthly')

const baht = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 })

const reduced = usePrefersReducedMotion()

const forecastSeries = computed(() =>
  mode.value === 'monthly' ? props.forecastMonthly : props.forecastCumulative,
)
const actualSeries = computed(() =>
  mode.value === 'monthly' ? props.actualMonthly : props.actualCumulative,
)

const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'พยากรณ์ (Forecast)',
      data: forecastSeries.value,
      borderColor: '#f59e0b',
      backgroundColor: 'rgba(245, 158, 11, 0.08)',
      borderDash: [6, 4],
      pointBackgroundColor: '#f59e0b',
      pointRadius: 3,
      tension: 0.3,
      fill: false,
    },
    {
      label: 'เบิกจ่ายจริง (Actual)',
      data: actualSeries.value,
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.12)',
      pointBackgroundColor: '#10b981',
      pointRadius: 3,
      tension: 0.3,
      fill: true,
    },
  ],
}))

const options = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: reduced.value ? false : undefined,
  interaction: { mode: 'index', intersect: false },
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
        label: (ctx: TooltipItem<'line'>) =>
          ` ${ctx.dataset.label}: ${baht.format(Number(ctx.parsed.y))} บาท`,
      },
    },
  },
  scales: {
    x: {
      grid: { color: '#334155' },
      border: { color: '#334155' },
      ticks: { color: '#94a3b8' },
    },
    y: {
      beginAtZero: true,
      grid: { color: '#334155' },
      border: { color: '#334155' },
      ticks: { color: '#94a3b8', callback: (value) => compact.format(Number(value)) },
    },
  },
}))
</script>

<template>
  <div class="space-y-3">
    <div class="flex justify-end">
      <div class="inline-flex rounded-lg border border-dark-border bg-dark-bg p-0.5 text-xs">
        <button
          type="button"
          class="rounded-md px-3 py-1.5 font-medium transition"
          :class="mode === 'monthly' ? 'bg-primary-600 text-white' : 'text-dark-muted hover:text-white'"
          @click="mode = 'monthly'"
        >
          รายเดือน
        </button>
        <button
          type="button"
          class="rounded-md px-3 py-1.5 font-medium transition"
          :class="mode === 'cumulative' ? 'bg-primary-600 text-white' : 'text-dark-muted hover:text-white'"
          @click="mode = 'cumulative'"
        >
          สะสม
        </button>
      </div>
    </div>
    <div class="h-72">
      <Line :data="chartData" :options="options" />
    </div>
  </div>
</template>

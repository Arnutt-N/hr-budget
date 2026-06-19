<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend,
} from 'chart.js'
import type { ChartData, ChartOptions, TooltipItem } from 'chart.js'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'

// Grouped bar needs the same registrations as MonthlyExpenditureChart (Bar).
ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

interface Props {
  labels: string[]
  budget: number[]
  disbursed: number[]
}
const props = defineProps<Props>()

const baht = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 })

const reduced = usePrefersReducedMotion()

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'งบ/จัดสรร',
      data: props.budget,
      backgroundColor: '#0ea5e9',
      hoverBackgroundColor: '#38bdf8',
      borderRadius: 6,
      maxBarThickness: 38,
    },
    {
      label: 'เบิกจ่าย',
      data: props.disbursed,
      backgroundColor: '#10b981',
      hoverBackgroundColor: '#34d399',
      borderRadius: 6,
      maxBarThickness: 38,
    },
  ],
}))

const options = computed<ChartOptions<'bar'>>(() => ({
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
  <div class="h-72">
    <Bar :data="chartData" :options="options" />
  </div>
</template>

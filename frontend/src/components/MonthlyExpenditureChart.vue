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

// Register only the pieces this bar chart needs (chart.js v4 is tree-shakeable).
ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

interface Props {
  labels: string[]
  data: number[]
  title?: string
}
const props = withDefaults(defineProps<Props>(), {
  title: 'เบิกจ่ายรายเดือน',
})

const baht = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 })

const reduced = usePrefersReducedMotion()

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'เบิกจ่าย',
      data: props.data,
      backgroundColor: '#0ea5e9',
      hoverBackgroundColor: '#38bdf8',
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
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1e293b',
      borderColor: '#334155',
      borderWidth: 1,
      titleColor: '#f1f5f9',
      bodyColor: '#94a3b8',
      padding: 10,
      callbacks: {
        label: (ctx: TooltipItem<'bar'>) => ` ${baht.format(Number(ctx.parsed.y))} บาท`,
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
      ticks: {
        color: '#94a3b8',
        callback: (value) => compact.format(Number(value)),
      },
    },
  },
}))
</script>

<template>
  <div class="h-72">
    <Bar :data="chartData" :options="options" :aria-label="title" />
  </div>
</template>

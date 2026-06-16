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

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

interface Props {
  labels: string[]
  values: number[]
  color?: string
  hoverColor?: string
}
const props = withDefaults(defineProps<Props>(), {
  color: '#0ea5e9',
  hoverColor: '#38bdf8',
})

const baht = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 })

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.labels,
  datasets: [
    {
      label: 'งบจัดสรร',
      data: props.values,
      backgroundColor: props.color,
      hoverBackgroundColor: props.hoverColor,
      borderRadius: 6,
      maxBarThickness: 32,
    },
  ],
}))

const options = computed<ChartOptions<'bar'>>(() => ({
  indexAxis: 'y', // horizontal bars — long Thai labels read better on the y-axis
  responsive: true,
  maintainAspectRatio: false,
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
        label: (ctx: TooltipItem<'bar'>) => ` ${baht.format(Number(ctx.parsed.x))} บาท`,
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
  <div class="h-72">
    <Bar :data="chartData" :options="options" />
  </div>
</template>

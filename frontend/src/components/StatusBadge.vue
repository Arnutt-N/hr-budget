<script setup lang="ts">
import type { RequestStatus } from '@/types/budget-request'
import { STATUS_LABELS } from '@/types/budget-request'

const props = defineProps<{ status: RequestStatus }>()

const colorMap: Record<RequestStatus, string> = {
  draft: 'bg-slate-500/10 text-slate-400',
  saved: 'bg-sky-500/10 text-sky-400',
  confirmed: 'bg-indigo-500/10 text-indigo-400',
  pending: 'bg-yellow-500/10 text-yellow-400',
  approved: 'bg-green-500/10 text-green-400',
  rejected: 'bg-red-500/10 text-red-400',
}
</script>

<template>
  <span
    :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', colorMap[props.status]]"
  >
    {{ STATUS_LABELS[props.status] }}
  </span>
</template>

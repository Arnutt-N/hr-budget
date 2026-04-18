<script setup lang="ts">
import type { RequestStatus } from '@/types/budget-request'
import { STATUS_LABELS } from '@/types/budget-request'

const props = defineProps<{ status: RequestStatus }>()

const colorMap: Record<RequestStatus, string> = {
  draft: 'bg-gray-100 text-gray-700',
  saved: 'bg-blue-100 text-blue-700',
  confirmed: 'bg-indigo-100 text-indigo-700',
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-700',
}
</script>

<template>
  <span
    :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', colorMap[props.status]]"
  >
    {{ STATUS_LABELS[props.status] }}
  </span>
</template>

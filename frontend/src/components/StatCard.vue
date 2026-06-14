<script setup lang="ts">
import type { Component } from 'vue'

type Accent = 'sky' | 'emerald' | 'amber' | 'violet' | 'rose'

interface Props {
  label: string
  value: string
  accent?: Accent
  sub?: string
  icon?: Component
}

withDefaults(defineProps<Props>(), { accent: 'sky' })

const ACCENTS: Record<Accent, string> = {
  sky: 'bg-sky-500/15 text-sky-400',
  emerald: 'bg-emerald-500/15 text-emerald-400',
  amber: 'bg-amber-500/15 text-amber-400',
  violet: 'bg-violet-500/15 text-violet-400',
  rose: 'bg-rose-500/15 text-rose-400',
}
</script>

<template>
  <div
    class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm transition hover:border-slate-600 hover:shadow-md"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="truncate text-sm text-dark-muted">{{ label }}</p>
        <p class="mt-2 text-2xl font-bold text-white">{{ value }}</p>
        <p v-if="sub" class="mt-1 truncate text-xs text-dark-muted">{{ sub }}</p>
      </div>
      <span
        v-if="icon"
        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
        :class="ACCENTS[accent]"
      >
        <component :is="icon" class="h-5 w-5" />
      </span>
    </div>
  </div>
</template>

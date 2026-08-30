<script setup lang="ts">
defineProps<{ title: string; subtitle?: string }>()
</script>

<template>
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
      <p v-if="subtitle" class="mt-1 text-sm text-dark-muted">{{ subtitle }}</p>
    </div>
    <slot />
  </div>
</template>

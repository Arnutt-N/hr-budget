<script setup lang="ts">
defineProps<{ title: string }>()
</script>

<template>
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{ message: string }>()
</script>

<template>
  <p class="py-4 text-center text-dark-muted">{{ message }}</p>
  <slot />
</template>

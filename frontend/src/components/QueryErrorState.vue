<script setup lang="ts">
import { computed } from 'vue'
import Message from 'primevue/message'

const props = defineProps<{ error?: unknown }>()

const message = computed(() =>
  props.error instanceof Error ? props.error.message : 'ไม่สามารถโหลดข้อมูลได้',
)
</script>

<template>
  <Message severity="error" :closable="false">{{ message }}</Message>
</template>

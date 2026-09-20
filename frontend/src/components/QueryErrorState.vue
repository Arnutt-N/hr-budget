<script setup lang="ts">
import { computed } from 'vue'
import Message from 'primevue/message'

const props = defineProps<{ error?: unknown; retry?: () => void }>()

const message = computed(() =>
  props.error instanceof Error ? props.error.message : 'ไม่สามารถโหลดข้อมูลได้',
)
</script>

<template>
  <div>
    <Message severity="error" :closable="false">{{ message }}</Message>
    <button
      v-if="retry"
      type="button"
      class="mt-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
      @click="retry"
    >
      ลองอีกครั้ง
    </button>
  </div>
</template>

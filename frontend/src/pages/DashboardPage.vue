<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="bg-dark-card border border-dark-border p-6 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold text-white mb-3">หน้าหลัก (Dashboard)</h2>
    <div class="space-y-2 text-dark-muted">
      <p>สวัสดี <strong>{{ auth.user?.name }}</strong></p>
      <p class="text-sm text-dark-muted">
        อีเมล: <span class="font-mono">{{ auth.user?.email }}</span>
      </p>
      <p class="text-sm text-dark-muted">
        บทบาท: <span class="font-mono">{{ auth.user?.role }}</span>
      </p>
    </div>
    <div class="mt-6 p-4 border border-dashed border-dark-border rounded-lg bg-dark-bg">
      <p class="text-xs text-dark-muted">
        🚧 Day 1 Foundation: Dashboard นี้เป็น placeholder — features จริง (stat cards, charts, ตารางคำขอ) จะเพิ่มใน Day 3-4
      </p>
    </div>
  </div>
</template>

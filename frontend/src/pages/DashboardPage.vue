<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="bg-white p-6 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold text-gray-900 mb-3">หน้าหลัก (Dashboard)</h2>
    <div class="space-y-2 text-gray-700">
      <p>สวัสดี <strong>{{ auth.user?.name }}</strong></p>
      <p class="text-sm text-gray-500">
        อีเมล: <span class="font-mono">{{ auth.user?.email }}</span>
      </p>
      <p class="text-sm text-gray-500">
        บทบาท: <span class="font-mono">{{ auth.user?.role }}</span>
      </p>
    </div>
    <div class="mt-6 p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50">
      <p class="text-xs text-gray-500">
        🚧 Day 1 Foundation: Dashboard นี้เป็น placeholder — features จริง (stat cards, charts, ตารางคำขอ) จะเพิ่มใน Day 3-4
      </p>
    </div>
  </div>
</template>

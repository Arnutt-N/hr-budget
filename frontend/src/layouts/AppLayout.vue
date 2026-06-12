<script setup lang="ts">
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'

const router = useRouter()
const auth = useAuthStore()

// User hydration happens in the router guard (auth.bootstrap) — by the time
// this layout renders, auth.user is already resolved.

async function onLogout(): Promise<void> {
  await auth.logout()
  await router.replace({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <span class="text-lg font-bold text-gray-900">HR Budget</span>
        </div>
        <nav class="flex items-center gap-4 text-sm">
          <RouterLink to="/dashboard" class="text-gray-600 hover:text-gray-900">แดชบอร์ด</RouterLink>
          <RouterLink to="/requests" class="text-gray-600 hover:text-gray-900">คำของบประมาณ</RouterLink>
          <template v-if="auth.user?.role === 'admin'">
            <span class="text-gray-300">|</span>
            <RouterLink to="/fiscal-years" class="text-gray-600 hover:text-gray-900">ปีงบประมาณ</RouterLink>
            <RouterLink to="/organizations" class="text-gray-600 hover:text-gray-900">หน่วยงาน</RouterLink>
            <RouterLink to="/categories" class="text-gray-600 hover:text-gray-900">หมวดงบประมาณ</RouterLink>
            <RouterLink to="/users" class="text-gray-600 hover:text-gray-900">จัดการผู้ใช้</RouterLink>
          </template>
        </nav>
        <div class="flex items-center gap-4 text-sm">
          <NotificationBell />
          <span class="text-gray-700">{{ auth.user?.name || 'Loading...' }}</span>
          <button
            class="text-red-600 hover:text-red-800 font-medium"
            @click="onLogout"
          >
            ออกจากระบบ
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto p-4 sm:p-6">
      <RouterView />
    </main>
  </div>
</template>

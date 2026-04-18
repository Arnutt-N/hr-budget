<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

// On first mount, refresh user info from server.
// Handles case where token is still valid but user data was stale.
onMounted(async () => {
  if (auth.isAuthenticated && !auth.user) {
    await auth.fetchMe()
  }
})

async function onLogout(): Promise<void> {
  auth.logout()
  await router.replace({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <span class="text-lg font-bold text-gray-900">HR Budget</span>
          <span class="text-xs text-gray-400 hidden sm:inline">Day 1 Foundation</span>
        </div>
        <div class="flex items-center gap-4 text-sm">
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

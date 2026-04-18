<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const loading = ref(false)

async function onSubmit(): Promise<void> {
  errorMsg.value = ''
  loading.value = true
  try {
    const result = await auth.login({ email: email.value, password: password.value })
    if (!result.ok) {
      errorMsg.value = result.error ?? 'เข้าสู่ระบบไม่สำเร็จ'
      return
    }
    const redirect = (route.query.redirect as string) || '/dashboard'
    await router.replace(redirect)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <form
      class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md space-y-5"
      @submit.prevent="onSubmit"
    >
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">HR Budget</h1>
        <p class="text-sm text-gray-500 mt-1">ระบบจัดการงบประมาณ</p>
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
        <input
          id="email"
          v-model.trim="email"
          type="email"
          autocomplete="email"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
        <input
          id="password"
          v-model="password"
          type="password"
          autocomplete="current-password"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
        />
      </div>

      <p v-if="errorMsg" class="text-red-600 text-sm" role="alert">{{ errorMsg }}</p>

      <button
        type="submit"
        :disabled="loading"
        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition disabled:opacity-60 disabled:cursor-not-allowed"
      >
        {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
      </button>
    </form>
  </div>
</template>

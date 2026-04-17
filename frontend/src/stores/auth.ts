import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginRequest, AuthResponse, ApiResponse } from '@/types/api'

const TOKEN_KEY = 'hr_budget_token'
const USER_KEY = 'hr_budget_user'

function readString(key: string): string {
  const v = localStorage.getItem(key)
  if (!v || v === 'undefined' || v === 'null') {
    localStorage.removeItem(key)
    return ''
  }
  return v
}

function readJson<T>(key: string, fallback: T | null = null): T | null {
  const v = localStorage.getItem(key)
  if (!v || v === 'undefined' || v === 'null') {
    localStorage.removeItem(key)
    return fallback
  }
  try {
    return JSON.parse(v) as T
  } catch {
    localStorage.removeItem(key)
    return fallback
  }
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string>(readString(TOKEN_KEY))
  const user = ref<User | null>(readJson<User>(USER_KEY))

  const isAuthenticated = computed(() => token.value !== '')

  async function login(payload: LoginRequest): Promise<{ ok: boolean; error?: string }> {
    const res = await fetch('/api/v1/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    const json = (await res.json()) as ApiResponse<AuthResponse>

    if (!json.success || !json.data) {
      const msg = json.details
        ? Object.values(json.details).join(', ')
        : (json.error ?? 'เข้าสู่ระบบไม่สำเร็จ')
      return { ok: false, error: msg }
    }

    token.value = json.data.token
    user.value = json.data.user
    localStorage.setItem(TOKEN_KEY, token.value)
    localStorage.setItem(USER_KEY, JSON.stringify(user.value))
    return { ok: true }
  }

  async function fetchMe(): Promise<boolean> {
    if (!token.value) return false
    const res = await fetch('/api/v1/auth/me', {
      headers: { Authorization: `Bearer ${token.value}` },
    })
    const json = (await res.json()) as ApiResponse<User>
    if (!json.success || !json.data) {
      logout()
      return false
    }
    user.value = json.data
    localStorage.setItem(USER_KEY, JSON.stringify(user.value))
    return true
  }

  function logout(): void {
    token.value = ''
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  return { token, user, isAuthenticated, login, fetchMe, logout }
})

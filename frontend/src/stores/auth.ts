import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, LoginRequest, AuthResponse, ApiResponse } from '@/types/api'

/**
 * Cookie-based auth: the JWT lives in an httpOnly cookie set by the backend,
 * so JS never sees it. The store only holds the user profile in memory;
 * after a page refresh bootstrap() asks /auth/me whether the cookie session
 * is still valid.
 */
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const initialized = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  /**
   * One-time session hydration, awaited by the router guard before the
   * first navigation resolves. A 401 here just means "not logged in".
   */
  async function bootstrap(): Promise<void> {
    if (initialized.value) return
    try {
      const res = await fetch('/api/v1/auth/me', {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
      const json = (await res.json()) as ApiResponse<User>
      user.value = json.success && json.data ? json.data : null
    } catch {
      user.value = null
    } finally {
      initialized.value = true
    }
  }

  async function login(payload: LoginRequest): Promise<{ ok: boolean; error?: string }> {
    const res = await fetch('/api/v1/auth/login', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    })
    const json = (await res.json()) as ApiResponse<AuthResponse>

    if (!json.success || !json.data) {
      const msg = json.details
        ? Object.values(json.details).join(', ')
        : (json.error ?? 'เข้าสู่ระบบไม่สำเร็จ')
      return { ok: false, error: msg }
    }

    user.value = json.data.user
    initialized.value = true
    return { ok: true }
  }

  /**
   * Clears the server cookie, then local state. Safe to call when already
   * logged out (used by apiFetch's 401 handler) — skips the network call.
   */
  async function logout(): Promise<void> {
    if (user.value !== null) {
      try {
        await fetch('/api/v1/auth/logout', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
      } catch {
        // Cookie clear failed (network down) — still drop local state.
      }
    }
    user.value = null
    // Force the next navigation to re-check the server: if the logout API
    // call failed, the cookie may still be valid and bootstrap() must see it.
    initialized.value = false
  }

  return { user, initialized, isAuthenticated, bootstrap, login, logout }
})

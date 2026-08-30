import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiUrl } from '@/api/base'
import { queryClient } from '@/lib/queryClient'
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
      const res = await fetch(apiUrl('/auth/me'), {
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
    let json: ApiResponse<AuthResponse>
    try {
      const res = await fetch(apiUrl('/auth/login'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
      })
      json = (await res.json()) as ApiResponse<AuthResponse>
    } catch {
      // Network failure / non-JSON response — surface it, don't silently no-op.
      return { ok: false, error: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้' }
    }

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
        await fetch(apiUrl('/auth/logout'), {
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
    // Drop every cached query (notifications, requests, permissions, ...) so
    // the next login on this tab can never render the previous user's data.
    queryClient.clear()
  }

  return { user, initialized, isAuthenticated, bootstrap, login, logout }
})

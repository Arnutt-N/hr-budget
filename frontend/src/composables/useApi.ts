import { useAuthStore } from '@/stores/auth'
import type { ApiResponse } from '@/types/api'

/**
 * Typed fetch wrapper. Automatically:
 *  - Prepends /api/v1
 *  - Sends the httpOnly auth cookie (credentials) + CSRF header
 *  - Drops local auth state on 401
 */
export async function apiFetch<T = unknown>(
  path: string,
  options: RequestInit = {},
  isFormData = false,
): Promise<ApiResponse<T>> {
  const auth = useAuthStore()
  const headers = new Headers(options.headers)
  if (!isFormData && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }
  // CSRF guard: backend rejects cookie-authed mutations without this header.
  headers.set('X-Requested-With', 'XMLHttpRequest')

  const res = await fetch(`/api/v1${path.startsWith('/') ? path : '/' + path}`, {
    ...options,
    credentials: 'same-origin',
    headers,
  })

  if (res.status === 401) {
    // logout() skips the network call when user is already null,
    // so a 401 from /auth/me during bootstrap cannot loop.
    void auth.logout()
  }

  // 204 No Content (e.g. DELETE) has no body — res.json() would throw
  if (res.status === 204) {
    return { success: true } as ApiResponse<T>
  }

  let body: ApiResponse<T>
  try {
    body = (await res.json()) as ApiResponse<T>
  } catch {
    return {
      success: false,
      error: res.status >= 500 ? 'เกิดข้อผิดพลาดในเซิร์ฟเวอร์' : `HTTP ${res.status}`,
    }
  }

  return body
}

export function useApi() {
  return { apiFetch }
}

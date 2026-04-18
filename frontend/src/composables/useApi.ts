import { useAuthStore } from '@/stores/auth'
import type { ApiResponse } from '@/types/api'

/**
 * Typed fetch wrapper. Automatically:
 *  - Prepends /api/v1
 *  - Attaches Bearer token when logged in
 *  - Logs out + redirects on 401
 */
export async function apiFetch<T = unknown>(
  path: string,
  options: RequestInit = {},
): Promise<ApiResponse<T>> {
  const auth = useAuthStore()
  const headers = new Headers(options.headers)
  if (!headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }
  if (auth.token) {
    headers.set('Authorization', `Bearer ${auth.token}`)
  }

  const res = await fetch(`/api/v1${path.startsWith('/') ? path : '/' + path}`, {
    ...options,
    headers,
  })

  if (res.status === 401) {
    auth.logout()
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

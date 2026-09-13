import { apiFetch } from '@/composables/useApi'
import type { ThaidStatus } from '@/types/api'

/**
 * Whether to offer the ThaID login button. The endpoint is public (no auth) and
 * always 200s, so any failure just hides the button — never blocks email login.
 */
export async function fetchThaidStatus(): Promise<ThaidStatus> {
  try {
    const res = await apiFetch<ThaidStatus>('/auth/thaid/status')
    return res.success && res.data ? res.data : { enabled: false, mock: false }
  } catch {
    return { enabled: false, mock: false }
  }
}

/**
 * One-time ThaID error flash (e.g. login failed / state expired). The backend
 * consumes the message on read, so it shows once. Best-effort: any failure
 * just shows nothing — never blocks the normal email login flow.
 */
export async function fetchThaidFlash(): Promise<string | null> {
  try {
    const res = await apiFetch<{ message: string | null }>('/auth/thaid/flash')
    return res.success ? (res.data?.message ?? null) : null
  } catch {
    return null
  }
}

/**
 * Full-page navigation target that starts the OAuth flow. Must be a real
 * navigation (window.location), NOT fetch — the authorization-code flow
 * redirects the browser out to DOPA and back. Trailing slash on the base is
 * trimmed so a subdirectory deploy never yields '//api/...'.
 */
export function thaidLoginUrl(): string {
  const base = ((import.meta.env.VITE_API_BASE_URL as string) || '').replace(/\/$/, '')
  return `${base}/api/v1/auth/thaid/login`
}

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
 * Full-page navigation target that starts the OAuth flow. Must be a real
 * navigation (window.location), NOT fetch — the authorization-code flow
 * redirects the browser out to DOPA and back. Trailing slash on the base is
 * trimmed so a subdirectory deploy never yields '//api/...'.
 */
export function thaidLoginUrl(): string {
  const base = ((import.meta.env.VITE_API_BASE_URL as string) || '').replace(/\/$/, '')
  return `${base}/api/v1/auth/thaid/login`
}

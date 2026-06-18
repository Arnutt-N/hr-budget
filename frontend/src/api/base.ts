/**
 * Single source of truth for where the JSON API lives.
 *
 * `VITE_API_BASE_URL` is the path the app is deployed under, WITHOUT the
 * `/api/v1` suffix. It is baked in at build time:
 *   - dev / root deploy:        ''                   → fetches /api/v1/*
 *   - subdirectory deploy:      '/hr-budget/public'  → /hr-budget/public/api/v1/*
 *
 * Kept dependency-free so both useApi (which imports the auth store) and the
 * auth store can import it without a circular reference.
 */
export const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL as string) || ''

/** Build an absolute-from-origin API URL: API_BASE_URL + /api/v1 + path. */
export function apiUrl(path: string): string {
  return `${API_BASE_URL}/api/v1${path.startsWith('/') ? path : '/' + path}`
}

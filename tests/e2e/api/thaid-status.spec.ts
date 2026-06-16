/**
 * ThaID status endpoint — E2E (API direct)
 *
 * Proves the feature is DORMANT BY DEFAULT: with no THAID_* credentials in the
 * test/CI environment, the public status endpoint must report enabled=false so
 * the SPA hides the ThaID button. This is the deterministic guard for the
 * config gate (the real OAuth round-trip can't run without live DOPA creds).
 *
 * Requires the backend on API_URL (default http://localhost:18080 in CI).
 */

import { test, expect } from '@playwright/test'

const API = process.env.API_URL || 'http://localhost:18080'

test.describe('ThaID status (config gate)', () => {
  test('GET /api/v1/auth/thaid/status returns dormant envelope when unconfigured', async ({ request }) => {
    const res = await request.get(`${API}/api/v1/auth/thaid/status`)
    expect(res.status()).toBe(200)

    const body = await res.json()
    expect(body.success).toBe(true)
    expect(body.data).toHaveProperty('enabled')
    expect(body.data).toHaveProperty('mock')
    // No THAID_* env in CI → feature must be off.
    expect(body.data.enabled).toBe(false)
  })

  test('status endpoint is public (no auth required)', async ({ request }) => {
    // Unlike /auth/me, status must not 401 — the login page calls it pre-auth.
    const res = await request.get(`${API}/api/v1/auth/thaid/status`)
    expect(res.status()).toBe(200)
  })
})

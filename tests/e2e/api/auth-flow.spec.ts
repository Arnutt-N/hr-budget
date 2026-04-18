/**
 * Day 1 Auth Flow — E2E test
 *
 * Verifies the full login journey:
 *   Vue SPA (5174) → POST /api/v1/auth/login → JWT stored → /dashboard
 *
 * Requires:
 *   - Backend running on API_URL (default http://localhost:18080 in CI,
 *     or http://hr_budget.test locally via Laragon)
 *   - Frontend running on BASE_URL (default http://localhost:5174)
 *   - Seed user: E2E_USER_EMAIL / E2E_USER_PASSWORD exists with given creds
 */

import { test, expect } from '@playwright/test'

const USER_EMAIL = process.env.E2E_USER_EMAIL || 'test@hr.local'
const USER_PASSWORD = process.env.E2E_USER_PASSWORD || 'pass1234'

test.describe('Auth Flow (Day 1)', () => {
  test.beforeEach(async ({ context }) => {
    // Start each test with no auth state
    await context.clearCookies()
  })

  test('redirects unauthenticated users from / to /login', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveURL(/\/login/)
    await expect(page.getByRole('heading', { name: 'HR Budget' })).toBeVisible()
  })

  test('shows validation error on empty submit', async ({ page }) => {
    await page.goto('/login')
    // Browser native "required" will block, but we check form is present
    await expect(page.getByLabel('อีเมล')).toBeVisible()
    await expect(page.getByLabel('รหัสผ่าน')).toBeVisible()
    await expect(page.getByRole('button', { name: /เข้าสู่ระบบ/ })).toBeVisible()
  })

  test('rejects invalid credentials with visible error', async ({ page }) => {
    await page.goto('/login')
    await page.getByLabel('อีเมล').fill('wrong@hr.local')
    await page.getByLabel('รหัสผ่าน').fill('badpassword')
    await page.getByRole('button', { name: /เข้าสู่ระบบ/ }).click()

    await expect(page.getByRole('alert')).toContainText('อีเมลหรือรหัสผ่าน')
    await expect(page).toHaveURL(/\/login/)
  })

  test('accepts valid credentials → redirects to /dashboard', async ({ page }) => {
    await page.goto('/login')
    await page.getByLabel('อีเมล').fill(USER_EMAIL)
    await page.getByLabel('รหัสผ่าน').fill(USER_PASSWORD)
    await page.getByRole('button', { name: /เข้าสู่ระบบ/ }).click()

    await expect(page).toHaveURL(/\/dashboard/)
    await expect(page.getByRole('heading', { name: /Dashboard/i })).toBeVisible()
  })

  test('persists session across page refresh (JWT in localStorage)', async ({ page }) => {
    // Login
    await page.goto('/login')
    await page.getByLabel('อีเมล').fill(USER_EMAIL)
    await page.getByLabel('รหัสผ่าน').fill(USER_PASSWORD)
    await page.getByRole('button', { name: /เข้าสู่ระบบ/ }).click()
    await expect(page).toHaveURL(/\/dashboard/)

    // Refresh → should stay logged in
    await page.reload()
    await expect(page).toHaveURL(/\/dashboard/)
    const token = await page.evaluate(() => localStorage.getItem('hr_budget_token'))
    expect(token).toBeTruthy()
    expect(token!.split('.').length).toBe(3) // JWT = header.payload.signature
  })

  test('logout clears session and returns to /login', async ({ page }) => {
    // Login first
    await page.goto('/login')
    await page.getByLabel('อีเมล').fill(USER_EMAIL)
    await page.getByLabel('รหัสผ่าน').fill(USER_PASSWORD)
    await page.getByRole('button', { name: /เข้าสู่ระบบ/ }).click()
    await expect(page).toHaveURL(/\/dashboard/)

    // Click logout
    await page.getByRole('button', { name: /ออกจากระบบ/ }).click()

    await expect(page).toHaveURL(/\/login/)
    const token = await page.evaluate(() => localStorage.getItem('hr_budget_token'))
    expect(token).toBeFalsy()
  })

  test('direct navigation to /dashboard while unauthenticated redirects to /login with redirect query', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page).toHaveURL(/\/login(\?|.*redirect)/)
  })
})

test.describe('API Direct (Day 1)', () => {
  const API = process.env.API_URL || 'http://localhost:18080'

  test('GET /api/v1/health returns 200 with envelope', async ({ request }) => {
    const res = await request.get(`${API}/api/v1/health`)
    expect(res.status()).toBe(200)
    const body = await res.json()
    expect(body.success).toBe(true)
    expect(body.data).toHaveProperty('version')
    expect(body.data).toHaveProperty('time')
  })

  test('POST /api/v1/auth/login with wrong password returns 401 + Thai message', async ({ request }) => {
    const res = await request.post(`${API}/api/v1/auth/login`, {
      data: { email: USER_EMAIL, password: 'wrong' },
    })
    expect(res.status()).toBe(401)
    const body = await res.json()
    expect(body.success).toBe(false)
    expect(body.error).toMatch(/อีเมลหรือรหัสผ่าน/)
  })

  test('POST /api/v1/auth/login with valid creds returns token + user', async ({ request }) => {
    const res = await request.post(`${API}/api/v1/auth/login`, {
      data: { email: USER_EMAIL, password: USER_PASSWORD },
    })
    expect(res.status()).toBe(200)
    const body = await res.json()
    expect(body.success).toBe(true)
    expect(body.data.token.split('.').length).toBe(3)
    expect(body.data.user.email).toBe(USER_EMAIL)
    expect(body.data).not.toHaveProperty('password')
    expect(body.data.user).not.toHaveProperty('password')
  })

  test('GET /api/v1/auth/me without Bearer returns 401', async ({ request }) => {
    const res = await request.get(`${API}/api/v1/auth/me`)
    expect(res.status()).toBe(401)
  })

  test('GET /api/v1/auth/me with Bearer returns user', async ({ request }) => {
    const login = await request.post(`${API}/api/v1/auth/login`, {
      data: { email: USER_EMAIL, password: USER_PASSWORD },
    })
    const { data } = await login.json()
    const token = data.token

    const me = await request.get(`${API}/api/v1/auth/me`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    expect(me.status()).toBe(200)
    const body = await me.json()
    expect(body.data.email).toBe(USER_EMAIL)
  })

  test('OPTIONS preflight returns 204 with CORS headers for allowed origin', async ({ request }) => {
    const res = await request.fetch(`${API}/api/v1/auth/login`, {
      method: 'OPTIONS',
      headers: {
        Origin: 'http://localhost:5174',
        'Access-Control-Request-Method': 'POST',
      },
    })
    expect(res.status()).toBe(204)
    expect(res.headers()['access-control-allow-origin']).toBe('http://localhost:5174')
  })
})

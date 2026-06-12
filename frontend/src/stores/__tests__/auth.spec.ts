import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '../auth'
import type { User } from '@/types/api'

const fakeUser: User = { id: 1, email: 'a@moj.go.th', name: 'A', role: 'admin' }

function mockFetchOnce(status: number, body: unknown): void {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      status,
      json: () => Promise.resolve(body),
    }),
  )
}

describe('auth store (cookie mode)', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
  })

  test('bootstrap with valid cookie session sets user', async () => {
    // Arrange
    mockFetchOnce(200, { success: true, data: fakeUser })
    const auth = useAuthStore()

    // Act
    await auth.bootstrap()

    // Assert
    expect(auth.user).toEqual(fakeUser)
    expect(auth.initialized).toBe(true)
    expect(auth.isAuthenticated).toBe(true)
  })

  test('bootstrap with no session leaves user null without throwing', async () => {
    mockFetchOnce(401, { success: false, error: 'Unauthorized' })
    const auth = useAuthStore()

    await auth.bootstrap()

    expect(auth.user).toBeNull()
    expect(auth.initialized).toBe(true)
    expect(auth.isAuthenticated).toBe(false)
  })

  test('bootstrap runs the network call only once', async () => {
    mockFetchOnce(200, { success: true, data: fakeUser })
    const auth = useAuthStore()

    await auth.bootstrap()
    await auth.bootstrap()

    expect(vi.mocked(fetch).mock.calls).toHaveLength(1)
  })

  test('login failure returns Thai error message and no user', async () => {
    mockFetchOnce(401, { success: false, error: 'อีเมลหรือรหัสผ่านไม่ถูกต้อง' })
    const auth = useAuthStore()

    const result = await auth.login({ email: 'a@moj.go.th', password: 'x' })

    expect(result.ok).toBe(false)
    expect(result.error).toBe('อีเมลหรือรหัสผ่านไม่ถูกต้อง')
    expect(auth.isAuthenticated).toBe(false)
  })

  test('login success stores user and marks initialized', async () => {
    mockFetchOnce(200, {
      success: true,
      data: { token: 't', expires_in: 3600, user: fakeUser },
    })
    const auth = useAuthStore()

    const result = await auth.login({ email: 'a@moj.go.th', password: 'pw' })

    expect(result.ok).toBe(true)
    expect(auth.user).toEqual(fakeUser)
    expect(auth.initialized).toBe(true)
  })

  test('logout clears user and calls the API once', async () => {
    mockFetchOnce(200, {
      success: true,
      data: { token: 't', expires_in: 3600, user: fakeUser },
    })
    const auth = useAuthStore()
    await auth.login({ email: 'a@moj.go.th', password: 'pw' })

    await auth.logout()

    expect(auth.user).toBeNull()
    expect(auth.isAuthenticated).toBe(false)
    // logout must force the next navigation to re-check the server
    expect(auth.initialized).toBe(false)
    // login + logout = 2 calls
    expect(vi.mocked(fetch).mock.calls).toHaveLength(2)
  })

  test('logout when already logged out skips the network call (no 401 loop)', async () => {
    mockFetchOnce(200, { success: true, data: fakeUser })
    const auth = useAuthStore()

    await auth.logout()

    expect(vi.mocked(fetch).mock.calls).toHaveLength(0)
  })
})

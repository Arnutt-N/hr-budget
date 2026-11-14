import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { apiFetch } from '../useApi'

const replaceMock = vi.fn()
let currentRouteMock = {
  fullPath: '/requests',
  matched: [{ meta: { requiresAuth: true } }],
}

vi.mock('@/router', () => ({
  default: {
    get currentRoute() {
      return { value: currentRouteMock }
    },
    replace: (...args: unknown[]) => replaceMock(...args),
  },
}))

describe('apiFetch 401 handling', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
    replaceMock.mockReset()
    currentRouteMock = {
      fullPath: '/requests',
      matched: [{ meta: { requiresAuth: true } }],
    }
  })

  test('on 401 calls logout and redirects to /login when route requiresAuth', async () => {
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce({
        status: 401,
        json: () => Promise.resolve({ success: false, error: 'Unauthorized' }),
      })
      .mockResolvedValueOnce({
        status: 200,
        json: () => Promise.resolve({ success: true }),
      })
    vi.stubGlobal('fetch', fetchMock)

    const res = await apiFetch('/requests')

    expect(res.success).toBe(false)
    expect(fetchMock).toHaveBeenCalledTimes(2)
    expect(fetchMock.mock.calls[1]?.[0]).toBe('/api/v1/auth/logout')
    expect(replaceMock).toHaveBeenCalledWith({
      name: 'login',
      query: { redirect: '/requests' },
    })
  })

  test('on 401 does not redirect when current route does not require auth', async () => {
    currentRouteMock = {
      fullPath: '/login',
      matched: [{ meta: { requiresAuth: false } }],
    }
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce({
        status: 401,
        json: () => Promise.resolve({ success: false, error: 'Unauthorized' }),
      })
      .mockResolvedValueOnce({
        status: 200,
        json: () => Promise.resolve({ success: true }),
      })
    vi.stubGlobal('fetch', fetchMock)

    await apiFetch('/auth/me')

    expect(replaceMock).not.toHaveBeenCalled()
  })
})

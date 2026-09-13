import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { fetchThaidFlash } from '../auth'

/** Stubs global fetch and returns a plain envelope (mirrors analytics.spec). */
function stubFetch(status: number, body: unknown): void {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      status,
      json: () => Promise.resolve(body),
    }),
  )
}

describe('fetchThaidFlash', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
  })

  test('returns the flash message from the envelope', async () => {
    stubFetch(200, { success: true, data: { message: 'เซสชันหมดอายุ' } })

    await expect(fetchThaidFlash()).resolves.toBe('เซสชันหมดอายุ')
  })

  test('returns null when the network call rejects (best-effort)', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('network down')))

    await expect(fetchThaidFlash()).resolves.toBeNull()
  })
})

import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { fetchComparison, fetchForecast, fetchRequestVsApproved } from '../analytics'

/** Stubs global fetch and returns a success envelope (mirrors rbac.spec). */
function stubFetch(status = 200, body: unknown = { success: true, data: {} }): void {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      status,
      json: () => Promise.resolve(body),
    }),
  )
}

function lastCall(): [string, RequestInit] {
  const calls = vi.mocked(fetch).mock.calls
  return calls[calls.length - 1] as unknown as [string, RequestInit]
}

describe('analytics api request shapes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
    stubFetch()
  })

  test('fetchComparison GETs comparison with fiscal_year + dimension', async () => {
    await fetchComparison(2569, 'quarter')
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/analytics/comparison?fiscal_year=2569&dimension=quarter')
    expect(init.method ?? 'GET').toBe('GET')
  })

  test('fetchComparison carries the year dimension', async () => {
    await fetchComparison(2570, 'year')
    expect(lastCall()[0]).toBe('/api/v1/analytics/comparison?fiscal_year=2570&dimension=year')
  })

  test('fetchComparison carries the month dimension', async () => {
    await fetchComparison(2569, 'month')
    expect(lastCall()[0]).toBe('/api/v1/analytics/comparison?fiscal_year=2569&dimension=month')
  })

  test('fetchForecast GETs forecast with fiscal_year', async () => {
    await fetchForecast(2569)
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/analytics/forecast?fiscal_year=2569')
    expect(init.method ?? 'GET').toBe('GET')
  })

  test('fetchRequestVsApproved GETs request-vs-approved with fiscal_year', async () => {
    await fetchRequestVsApproved(2569)
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/analytics/request-vs-approved?fiscal_year=2569')
    expect(init.method ?? 'GET').toBe('GET')
  })
})

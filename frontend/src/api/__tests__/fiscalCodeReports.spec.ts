import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { fetchFiscalCodeReport } from '../fiscalCodeReports'

const BASE = '/reports/fiscal-codes'

function stubFetch(status = 200, body: unknown = { success: true, data: [] }): void {
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

describe('fetchFiscalCodeReport request shapes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
    stubFetch()
  })

  test('sends year as a query parameter for year-filtered reports', async () => {
    await fetchFiscalCodeReport({ path: `${BASE}/by-activity`, hasAllocation: false, year: 2569 })
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/reports/fiscal-codes/by-activity?year=2569')
    expect(init.method ?? 'GET').toBe('GET')
    expect(new Headers(init.headers).get('X-Requested-With')).toBe('XMLHttpRequest')
  })

  test('omits the year parameter for year-independent reports', async () => {
    await fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false })
    expect(lastCall()[0]).toBe('/api/v1/reports/fiscal-codes/by-agency')
  })

  test('omits the year parameter when none is supplied', async () => {
    await fetchFiscalCodeReport({ path: `${BASE}/by-expense-category`, hasAllocation: true })
    expect(lastCall()[0]).toBe('/api/v1/reports/fiscal-codes/by-expense-category')
  })
})

describe('fetchFiscalCodeReport boundary normalization', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
  })

  test('normalizes activity_code into code and keeps a null name', async () => {
    stubFetch(200, {
      success: true,
      data: [{ activity_code: '01', name_th: null, count: 3 }],
    })
    const rows = await fetchFiscalCodeReport({ path: `${BASE}/by-activity`, hasAllocation: false, year: 2569 })
    expect(rows).toEqual([{ code: '01', name_th: null, count: 3 }])
    expect(rows[0]).not.toHaveProperty('allocated_pba')
  })

  test('normalizes parent_code and agency_code into code', async () => {
    stubFetch(200, {
      success: true,
      data: [
        { parent_code: 'P1', name_th: 'แม่', count: 2 },
        { agency_code: 'A1', name_th: null, count: 5 },
      ],
    })
    const rows = await fetchFiscalCodeReport({ path: `${BASE}/by-parent-rollup`, hasAllocation: false })
    expect(rows).toEqual([
      { code: 'P1', name_th: 'แม่', count: 2 },
      { code: 'A1', name_th: null, count: 5 },
    ])
  })

  test('retains allocated_pba only for the expense-category report', async () => {
    stubFetch(200, {
      success: true,
      data: [{ code: 'E1', name_th: 'หมวด', count: 4, allocated_pba: 12500 }],
    })
    const rows = await fetchFiscalCodeReport({
      path: `${BASE}/by-expense-category`,
      hasAllocation: true,
      year: 2569,
    })
    expect(rows[0]).toHaveProperty('allocated_pba', 12500)
  })

  test('converts decimal strings from MySQL into numbers', async () => {
    stubFetch(200, {
      success: true,
      data: [{ code: 'E1', name_th: 'หมวด', count: '4', allocated_pba: '12500.50' }],
    })
    const rows = await fetchFiscalCodeReport({
      path: `${BASE}/by-expense-category`,
      hasAllocation: true,
      year: 2569,
    })
    expect(rows).toEqual([{ code: 'E1', name_th: 'หมวด', count: 4, allocated_pba: 12500.5 }])
  })

  test('drops allocated_pba from a non-expense-category payload', async () => {
    stubFetch(200, {
      success: true,
      data: [{ code: 'A1', name_th: null, count: 1, allocated_pba: 999 }],
    })
    const rows = await fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false })
    expect(rows[0]).not.toHaveProperty('allocated_pba')
  })

  test('rejects a payload whose data is not an array', async () => {
    stubFetch(200, { success: true, data: { code: 'X' } })
    await expect(
      fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false }),
    ).rejects.toThrow('ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง')
  })

  test('rejects a row with no string code', async () => {
    stubFetch(200, { success: true, data: [{ name_th: 'ไม่มีรหัส', count: 1 }] })
    await expect(
      fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false }),
    ).rejects.toThrow('ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง')
  })

  test('rejects a row with a non-finite count', async () => {
    stubFetch(200, { success: true, data: [{ code: 'A1', name_th: null, count: 'many' }] })
    await expect(
      fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false }),
    ).rejects.toThrow('ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง')
  })

  test('rejects an expense-category row with a non-finite allocation', async () => {
    stubFetch(200, {
      success: true,
      data: [{ code: 'E1', name_th: 'หมวด', count: 1, allocated_pba: 'many' }],
    })
    await expect(
      fetchFiscalCodeReport({ path: `${BASE}/by-expense-category`, hasAllocation: true }),
    ).rejects.toThrow('ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง')
  })

  test('surfaces the server error message on a failure envelope', async () => {
    stubFetch(500, { success: false, error: 'เกิดข้อผิดพลาดในระบบ' })
    await expect(
      fetchFiscalCodeReport({ path: `${BASE}/by-agency`, hasAllocation: false }),
    ).rejects.toThrow('เกิดข้อผิดพลาดในระบบ')
  })
})

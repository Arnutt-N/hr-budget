import { expect, test, type Page, type Request } from '@playwright/test'

const ADMIN_EMAIL = 'admin@moj.go.th'
const ADMIN_PASSWORD = 'admin123'

const baseUrl = new URL(process.env.BASE_URL || 'http://localhost:5174')
const appPrefix = baseUrl.pathname.replace(/\/$/, '')
const apiPrefix = appPrefix.replace(/\/app$/, '')

function apiPath(path: string): string {
  return `${apiPrefix}${path}`
}

function fiscalReportPath(request: Request): string | null {
  const url = new URL(request.url())
  const marker = '/reports/fiscal-codes/'
  const index = url.pathname.indexOf(marker)
  return index >= 0 ? url.pathname.slice(index - '/api/v1'.length) : null
}

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto(`${appPrefix}/login`)
  await page.fill('input[name="email"]', ADMIN_EMAIL)
  await page.fill('input[name="password"]', ADMIN_PASSWORD)
  await page.click('button[type="submit"]')
  await page.waitForURL('**/dashboard')
}

test('fiscal code report smoke', async ({ page }) => {
  const requests: Request[] = []
  page.on('request', (request) => {
    if (fiscalReportPath(request)) requests.push(request)
  })

  await loginAsAdmin(page)

  const consoleErrors: string[] = []
  page.on('console', (message) => {
    if (message.type() !== 'error') return
    const text = message.text()
    if (text.includes('ERR_NAME_NOT_RESOLVED')) return
    consoleErrors.push(text)
  })

  const yearsResponse = await page.request.get(apiPath('/api/v1/reports/fiscal-codes/years'))
  expect(yearsResponse.status()).toBe(200)
  const years = (await yearsResponse.json()).data as { year: number; is_current: boolean }[]
  const currentYear = years.find((year) => year.is_current)?.year
  expect(currentYear).toBeDefined()

  await page.goto(`${appPrefix}/fiscal-code-reports`)
  await expect(page.getByRole('heading', { name: 'รหัสงบประมาณ' })).toBeVisible()
  await expect.poll(() => requests.length).toBe(5)
  await expect(page.getByText(`ประจำปีงบ พ.ศ. ${currentYear}`)).toBeVisible()

  const byPath = new Map<string, URL>()
  for (const request of requests) {
    const path = fiscalReportPath(request)
    if (path) byPath.set(path, new URL(request.url()))
  }

  expect(byPath.has('/api/v1/reports/fiscal-codes/years')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-expense-category')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-activity')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-parent-rollup')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-agency')).toBe(true)
  expect(byPath.get('/api/v1/reports/fiscal-codes/by-expense-category')!.searchParams.get('year')).toBe(String(currentYear))
  expect(byPath.get('/api/v1/reports/fiscal-codes/by-activity')!.searchParams.get('year')).toBe(String(currentYear))

  await expect(page.getByText('ปีที่เลือกใช้เฉพาะยอดจัดสรร จำนวนรหัสไม่กรองตามปี')).toBeVisible()
  await expect(page.getByText('จำนวนแถวแผนในปีที่เลือก')).toBeVisible()
  expect(await page.getByText('ไม่กรองตามปี', { exact: true }).count()).toBe(2)

  for (const name of ['by-parent-rollup', 'by-agency']) {
    expect(byPath.get(`/api/v1/reports/fiscal-codes/${name}`)!.searchParams.has('year')).toBe(false)
  }

  const expenseResponse = await page.request.get(apiPath('/api/v1/reports/fiscal-codes/by-expense-category'))
  expect(expenseResponse.status()).toBe(200)
  const expenseEnvelope = await expenseResponse.json()
  const expenseRows = expenseEnvelope.data
  expect(Array.isArray(expenseRows)).toBe(true)
  expect(expenseRows.length).toBeGreaterThan(0)
  expect(typeof expenseRows[0].allocated_pba).toBe('string')

  await expect(page.getByRole('heading', { name: 'รหัสตามประเภทรายจ่าย' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'รหัสตามกิจกรรมหลัก' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'รหัสลูกต่อรหัสแม่' })).toBeVisible()
  await expect(page.getByRole('heading', { name: 'รหัสลูกตามหน่วยงาน' })).toBeVisible()
  await expect(page.getByText('ไม่กรองตามปี').first()).toBeVisible()

  const amountColumn = page.getByRole('columnheader', { name: 'ยอดจัดสรร (บาท)' })
  await expect(amountColumn).toBeVisible()

  await page.screenshot({ path: 'test-results/fiscal-code-reports-smoke.png', fullPage: true })
  expect(consoleErrors).toEqual([])
})

test('fiscal code report requires authentication', async ({ request }) => {
  for (const name of ['by-expense-category', 'by-activity', 'by-parent-rollup', 'by-agency']) {
    const response = await request.get(apiPath(`/api/v1/reports/fiscal-codes/${name}`))
    expect(response.status()).toBe(401)
  }
})

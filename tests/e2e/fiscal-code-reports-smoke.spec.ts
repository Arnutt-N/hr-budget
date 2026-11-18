import { expect, test, type Page, type Request } from '@playwright/test'

const ADMIN_EMAIL = 'admin@moj.go.th'
const ADMIN_PASSWORD = 'admin123'

function fiscalReportPath(request: Request): string | null {
  const url = new URL(request.url())
  return url.pathname.includes('/reports/fiscal-codes/') ? url.pathname : null
}

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/login')
  await page.fill('input[name="email"]', ADMIN_EMAIL)
  await page.fill('input[name="password"]', ADMIN_PASSWORD)
  await page.click('button[type="submit"]')
  await page.waitForURL('**/dashboard')
}

test('fiscal code report smoke', async ({ page }) => {
  const consoleErrors: string[] = []
  page.on('console', (message) => {
    if (message.type() !== 'error') return
    const text = message.text()
    if (text.includes('ERR_NAME_NOT_RESOLVED')) return
    consoleErrors.push(text)
  })

  const requests: Request[] = []
  page.on('request', (request) => {
    if (fiscalReportPath(request)) requests.push(request)
  })

  await loginAsAdmin(page)
  await page.goto('/fiscal-code-reports')
  await expect(page.getByRole('heading', { name: 'รหัสงบประมาณ' })).toBeVisible()
  await expect.poll(() => requests.length).toBe(4)

  const byPath = new Map<string, URL>()
  for (const request of requests) {
    const path = fiscalReportPath(request)
    if (path) byPath.set(path, new URL(request.url()))
  }

  expect(byPath.has('/api/v1/reports/fiscal-codes/by-expense-category')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-activity')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-parent-rollup')).toBe(true)
  expect(byPath.has('/api/v1/reports/fiscal-codes/by-agency')).toBe(true)

  const selectedYearText = (await page.getByRole('combobox', { name: 'เลือกปีงบประมาณ' }).textContent()) ?? ''
  const selectedYear = selectedYearText.match(/\d{4}/)?.[0] ?? ''
  expect(selectedYear).toMatch(/^\d{4}$/)
  await expect(page.getByText(`ประจำปีงบ พ.ศ. ${selectedYear}`)).toBeVisible()
  await expect(page.getByText('ปีที่เลือกใช้เฉพาะยอดจัดสรร จำนวนรหัสไม่กรองตามปี')).toBeVisible()
  await expect(page.getByText('จำนวนแถวแผนในปีที่เลือก')).toBeVisible()
  expect(await page.getByText('ไม่กรองตามปี', { exact: true }).count()).toBe(2)

  const expenseUrl = byPath.get('/api/v1/reports/fiscal-codes/by-expense-category')!
  const activityUrl = byPath.get('/api/v1/reports/fiscal-codes/by-activity')!
  expect(expenseUrl.searchParams.get('year')).toBe(selectedYear)
  expect(activityUrl.searchParams.get('year')).toBe(selectedYear)

  for (const name of ['by-parent-rollup', 'by-agency']) {
    expect(byPath.get(`/api/v1/reports/fiscal-codes/${name}`)!.searchParams.has('year')).toBe(false)
  }

  const expenseResponse = await page.request.get(`/api/v1/reports/fiscal-codes/by-expense-category?year=${selectedYear}`)
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
  const amountCells = page.locator('td').filter({ hasText: /฿|บาท/ })
  expect(await amountCells.count()).toBeGreaterThan(0)

  const consoleErrors: string[] = []
  page.on('console', (message) => {
    if (message.type() !== 'error') return
    const text = message.text()
    if (text.includes('ERR_NAME_NOT_RESOLVED')) return
    consoleErrors.push(text)
  })
  await page.screenshot({ path: 'test-results/fiscal-code-reports-smoke.png', fullPage: true })
  expect(consoleErrors).toEqual([])
})

test('fiscal code report requires authentication', async ({ request }) => {
  for (const name of ['by-expense-category', 'by-activity', 'by-parent-rollup', 'by-agency']) {
    const response = await request.get(`/api/v1/reports/fiscal-codes/${name}`)
    expect(response.status()).toBe(401)
  }
})
})

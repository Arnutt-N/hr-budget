import { afterEach, describe, expect, test, vi } from 'vitest'

describe('apiUrl and download/export URL helpers', () => {
  afterEach(() => {
    vi.unstubAllEnvs()
    vi.resetModules()
  })

  test('apiUrl handles leading slash and empty base', async () => {
    vi.stubEnv('VITE_API_BASE_URL', '')
    vi.resetModules()
    const { apiUrl } = await import('../base')

    expect(apiUrl('/requests')).toBe('/api/v1/requests')
    expect(apiUrl('requests')).toBe('/api/v1/requests')
  })

  test('strips trailing slashes from VITE_API_BASE_URL across all URL helpers', async () => {
    vi.stubEnv('VITE_API_BASE_URL', '/hr_budget/public///')
    vi.resetModules()

    const { apiUrl } = await import('../base')
    const { executionExportUrl } = await import('../budgetExecution')
    const { fileDownloadUrl } = await import('../files')
    const { vaultFileDownloadUrl } = await import('../vault')

    expect(apiUrl('/auth/me')).toBe('/hr_budget/public/api/v1/auth/me')
    expect(executionExportUrl(2569, 5)).toBe(
      '/hr_budget/public/api/v1/budget-execution/export?year=2569&org=5',
    )
    expect(fileDownloadUrl(42)).toBe('/hr_budget/public/api/v1/files/42/download')
    expect(vaultFileDownloadUrl(7)).toBe('/hr_budget/public/api/v1/vault/files/7/download')
  })
})

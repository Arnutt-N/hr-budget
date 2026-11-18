import { describe, expect, test, vi } from 'vitest'
import { ref } from 'vue'
import { mount, flushPromises } from '@vue/test-utils'
import FiscalCodeReportPage from '../FiscalCodeReportPage.vue'

vi.mock('@/queries/fiscalCodeReports', async () => {
  const { ref: vueRef } = await import('vue')
  return {
    useFiscalCodeReportYears: () => ({
      data: vueRef([
        { year: 2568, is_current: false },
        { year: 2569, is_current: true },
      ]),
      error: vueRef(null),
      retry: vi.fn(),
    }),
    useFiscalCodeReports: () => ({ sections: vueRef([]) }),
  }
})

vi.mock('@/queries/useFiscalYears', () => ({
  useFiscalYearList: () => ({ data: { value: [] } }),
}))

describe('FiscalCodeReportPage year selector', () => {
  test('selects current year from report years and renders report queries', async () => {
    const wrapper = mount(FiscalCodeReportPage, {
      global: {
        stubs: {
          PageHeader: false,
          Select: { name: 'Select', props: ['modelValue', 'options'], template: '<div />' },
          DataTable: true,
          Column: true,
          QueryErrorState: true,
        },
      },
    })
    await flushPromises()

    const select = wrapper.findComponent({ name: 'Select' })
    expect(select.props('modelValue')).toBe(2569)
    expect((select.props('options') as { value: number }[]).map((option) => option.value)).toEqual([2569, 2568])
  })
})

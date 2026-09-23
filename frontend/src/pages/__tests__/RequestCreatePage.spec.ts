import { describe, expect, test, vi, beforeEach } from 'vitest'
import { ref } from 'vue'
import { mount, flushPromises } from '@vue/test-utils'
import RequestCreatePage from '../RequestCreatePage.vue'

const { pushMock, toastAddMock, createMock, submitMock } = vi.hoisted(() => ({
  pushMock: vi.fn(),
  toastAddMock: vi.fn(),
  createMock: vi.fn(),
  submitMock: vi.fn(),
}))

vi.mock('vue-router', () => ({ useRouter: () => ({ push: pushMock }) }))
vi.mock('primevue/usetoast', () => ({ useToast: () => ({ add: toastAddMock }) }))
vi.mock('@/queries/useBudgetRequests', () => ({
  useCreateBudgetRequest: () => ({ mutateAsync: createMock, isPending: ref(false) }),
  useSubmitBudgetRequest: () => ({ mutateAsync: submitMock, isPending: ref(false) }),
}))
vi.mock('@/queries/useFiscalYears', () => ({
  fiscalYearLabel: (fy: { year: number }) => `ปีงบ ${fy.year}`,
  useFiscalYearList: () => ({ data: ref([{ id: 1, year: 2569, is_current: true }]) }),
}))
vi.mock('@/queries/useOrganizations', () => ({
  useOrganizationList: () => ({ data: ref([]) }),
}))

describe('RequestCreatePage submit-once (double-submit regression)', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    createMock.mockResolvedValue({ id: 7 })
    submitMock.mockResolvedValue(undefined)
  })

  test('one user submit creates exactly one request', async () => {
    // Arrange
    const wrapper = mount(RequestCreatePage, {
      global: { stubs: { 'router-link': true } },
    })
    await wrapper.find('#req-title').setValue('คำขอทดสอบ')
    await wrapper.find('input[aria-label="แถว 1 ชื่อรายการ"]').setValue('ครุภัณฑ์')

    // Act — emulate browser event order for a real user click on a submit
    // button (happy-dom runs neither side of the activation chain, so dispatch
    // both explicitly): click first, then the submit it would cause.
    // Note: real browsers would NOT actually double-POST here — TanStack sets
    // isPending synchronously, Vue flushes :disabled in the microtask checkpoint
    // before activation behavior, and the disabled button suppresses submit.
    // This spec guards the single-binding wiring invariant instead.
    await wrapper.find('button[type="submit"]').trigger('click')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    // Assert
    expect(createMock).toHaveBeenCalledTimes(1)
    expect(submitMock).toHaveBeenCalledTimes(1)
    expect(pushMock).toHaveBeenCalledWith('/requests/7')
  })
})

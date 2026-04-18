import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { BudgetRequest, ListFilters, ListMeta, CreateBudgetRequest, UpdateBudgetRequest } from '@/types/budget-request'
import {
  fetchRequests,
  fetchRequestById,
  createRequest,
  updateRequest,
  deleteRequest,
  submitRequest,
  approveRequest,
  rejectRequest,
} from '@/api/budgetRequests'

export const useBudgetRequestStore = defineStore('budgetRequests', () => {
  const requests = ref<BudgetRequest[]>([])
  const currentRequest = ref<BudgetRequest | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const meta = ref<ListMeta | null>(null)
  const filters = ref<ListFilters>({})

  const totalPages = computed(() => meta.value?.total_pages ?? 0)
  const currentPage = computed(() => meta.value?.page ?? 1)

  async function fetchList(newFilters?: ListFilters): Promise<boolean> {
    if (newFilters) filters.value = { ...newFilters }
    loading.value = true
    error.value = null

    try {
      const res = await fetchRequests(filters.value)
      if (res.success && res.data) {
        requests.value = res.data
        meta.value = (res.meta as ListMeta | undefined) ?? null
        return true
      }
      error.value = res.error ?? 'เกิดข้อผิดพลาด'
      return false
    } catch {
      error.value = 'ไม่สามารถโหลดข้อมูลได้'
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchById(id: number): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const res = await fetchRequestById(id)
      if (res.success && res.data) {
        currentRequest.value = res.data
        return true
      }
      error.value = res.error ?? 'ไม่พบคำขอ'
      return false
    } catch {
      error.value = 'ไม่สามารถโหลดข้อมูลได้'
      return false
    } finally {
      loading.value = false
    }
  }

  async function create(data: CreateBudgetRequest): Promise<{ ok: boolean; id?: number; error?: string }> {
    loading.value = true
    error.value = null

    try {
      const res = await createRequest(data)
      if (res.success && res.data) {
        return { ok: true, id: res.data.id }
      }
      const msg = res.error ?? 'ไม่สามารถสร้างคำขอได้'
      error.value = msg
      return { ok: false, error: msg }
    } catch {
      const msg = 'เกิดข้อผิดพลาดในการสร้างคำขอ'
      error.value = msg
      return { ok: false, error: msg }
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: UpdateBudgetRequest): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    error.value = null

    try {
      const res = await updateRequest(id, data)
      if (res.success && res.data) {
        currentRequest.value = res.data
        return { ok: true }
      }
      const msg = res.error ?? 'ไม่สามารถแก้ไขคำขอได้'
      error.value = msg
      return { ok: false, error: msg }
    } catch {
      const msg = 'เกิดข้อผิดพลาดในการแก้ไขคำขอ'
      error.value = msg
      return { ok: false, error: msg }
    } finally {
      loading.value = false
    }
  }

  async function remove(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteRequest(id)
      if (res.success) {
        requests.value = requests.value.filter((r) => r.id !== id)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบคำขอได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาดในการลบคำขอ' }
    } finally {
      loading.value = false
    }
  }

  async function submit(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await submitRequest(id)
      if (res.success && res.data) {
        currentRequest.value = res.data
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถส่งอนุมัติได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาดในการส่งอนุมัติ' }
    } finally {
      loading.value = false
    }
  }

  async function approve(id: number, note?: string): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await approveRequest(id, note)
      if (res.success && res.data) {
        currentRequest.value = res.data
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถอนุมัติได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาดในการอนุมัติ' }
    } finally {
      loading.value = false
    }
  }

  async function reject(id: number, note: string): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await rejectRequest(id, note)
      if (res.success && res.data) {
        currentRequest.value = res.data
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถปฏิเสธได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาดในการปฏิเสธ' }
    } finally {
      loading.value = false
    }
  }

  return {
    requests,
    currentRequest,
    loading,
    error,
    meta,
    filters,
    totalPages,
    currentPage,
    fetchList,
    fetchById,
    create,
    update,
    remove,
    submit,
    approve,
    reject,
  }
})

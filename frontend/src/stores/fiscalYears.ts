import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { FiscalYear, CreateFiscalYear, UpdateFiscalYear } from '@/types/fiscal-year'
import {
  fetchFiscalYears,
  createFiscalYear,
  updateFiscalYear,
  deleteFiscalYear,
  setCurrentFiscalYear,
} from '@/api/fiscalYears'

export const useFiscalYearStore = defineStore('fiscalYears', () => {
  const fiscalYears = ref<FiscalYear[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchList(): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const res = await fetchFiscalYears()
      if (res.success && res.data) {
        fiscalYears.value = res.data
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

  async function create(data: CreateFiscalYear): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await createFiscalYear(data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถสร้างปีงบประมาณได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: UpdateFiscalYear): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await updateFiscalYear(id, data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถแก้ไขปีงบประมาณได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function remove(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteFiscalYear(id)
      if (res.success) {
        fiscalYears.value = fiscalYears.value.filter((fy) => fy.id !== id)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบปีงบประมาณได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function setCurrent(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await setCurrentFiscalYear(id)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถตั้งเป็นปีปัจจุบันได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  return { fiscalYears, loading, error, fetchList, create, update, remove, setCurrent }
})

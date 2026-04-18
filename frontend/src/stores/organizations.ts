import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Organization, CreateOrganization, UpdateOrganization } from '@/types/organization'
import {
  fetchOrganizations,
  createOrganization,
  updateOrganization,
  deleteOrganization,
} from '@/api/organizations'

export const useOrganizationStore = defineStore('organizations', () => {
  const organizations = ref<Organization[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchList(): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const res = await fetchOrganizations()
      if (res.success && res.data) {
        organizations.value = res.data
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

  async function create(data: CreateOrganization): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await createOrganization(data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถสร้างหน่วยงานได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: UpdateOrganization): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await updateOrganization(id, data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถแก้ไขหน่วยงานได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function remove(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteOrganization(id)
      if (res.success) {
        organizations.value = organizations.value.filter((o) => o.id !== id)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบหน่วยงานได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  return { organizations, loading, error, fetchList, create, update, remove }
})

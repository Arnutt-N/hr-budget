import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { User, CreateUser, UpdateUser } from '@/types/user'
import { fetchUsers, createUser, updateUser, deleteUser } from '@/api/users'

export const useUserStore = defineStore('users', () => {
  const users = ref<User[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchList(): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const res = await fetchUsers()
      if (res.success && res.data) {
        users.value = res.data
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

  async function create(data: CreateUser): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await createUser(data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถสร้างผู้ใช้ได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: UpdateUser): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await updateUser(id, data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถแก้ไขผู้ใช้ได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function remove(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteUser(id)
      if (res.success) {
        users.value = users.value.filter((u) => u.id !== id)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบผู้ใช้ได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  return { users, loading, error, fetchList, create, update, remove }
})

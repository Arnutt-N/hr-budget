import { ref } from 'vue'
import { defineStore } from 'pinia'
import type {
  BudgetCategory,
  BudgetCategoryItem,
  CreateCategory,
  UpdateCategory,
  CreateCategoryItem,
  UpdateCategoryItem,
} from '@/types/budget-category'
import {
  fetchCategories,
  createCategory,
  updateCategory,
  deleteCategory,
  fetchCategoryItems,
  createCategoryItem,
  updateCategoryItem,
  deleteCategoryItem,
  restoreCategoryItem,
} from '@/api/budgetCategories'

export const useBudgetCategoryStore = defineStore('budgetCategories', () => {
  const categories = ref<BudgetCategory[]>([])
  const currentItems = ref<BudgetCategoryItem[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchList(): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const res = await fetchCategories()
      if (res.success && res.data) {
        categories.value = res.data
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

  async function createCat(data: CreateCategory): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await createCategory(data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถสร้างหมวดได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function updateCat(id: number, data: UpdateCategory): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await updateCategory(id, data)
      if (res.success) {
        await fetchList()
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถแก้ไขหมวดได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function removeCat(id: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteCategory(id)
      if (res.success) {
        categories.value = categories.value.filter((c) => c.id !== id)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบหมวดได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function fetchItems(categoryId: number): Promise<boolean> {
    loading.value = true
    try {
      const res = await fetchCategoryItems(categoryId)
      if (res.success && res.data) {
        currentItems.value = res.data
        return true
      }
      return false
    } catch {
      return false
    } finally {
      loading.value = false
    }
  }

  async function createItem(categoryId: number, data: CreateCategoryItem): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await createCategoryItem(categoryId, data)
      if (res.success) {
        await fetchItems(categoryId)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถสร้างรายการได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function updateItem(categoryId: number, itemId: number, data: UpdateCategoryItem): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await updateCategoryItem(categoryId, itemId, data)
      if (res.success) {
        await fetchItems(categoryId)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถแก้ไขรายการได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function removeItem(categoryId: number, itemId: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await deleteCategoryItem(categoryId, itemId)
      if (res.success) {
        await fetchItems(categoryId)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถลบรายการได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  async function restoreItem(categoryId: number, itemId: number): Promise<{ ok: boolean; error?: string }> {
    loading.value = true
    try {
      const res = await restoreCategoryItem(categoryId, itemId)
      if (res.success) {
        await fetchItems(categoryId)
        return { ok: true }
      }
      return { ok: false, error: res.error ?? 'ไม่สามารถกู้คืนรายการได้' }
    } catch {
      return { ok: false, error: 'เกิดข้อผิดพลาด' }
    } finally {
      loading.value = false
    }
  }

  return {
    categories, currentItems, loading, error,
    fetchList, createCat, updateCat, removeCat,
    fetchItems, createItem, updateItem, removeItem, restoreItem,
  }
})

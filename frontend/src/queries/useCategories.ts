import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { toValue, type MaybeRefOrGetter } from 'vue'
import type {
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

const QUERY_KEY = ['categories'] as const
const itemsKey = (categoryId: number) => ['categories', categoryId, 'items'] as const

// --- Categories ---

export function useCategoryList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchCategories()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateCategory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateCategory) => {
      const res = await createCategory(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างหมวดได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateCategory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateCategory }) => {
      const res = await updateCategory(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขหมวดได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteCategory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteCategory(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบหมวดได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

// --- Category Items (per expanded category row) ---

export function useCategoryItems(categoryId: MaybeRefOrGetter<number>) {
  return useQuery({
    queryKey: ['categories', categoryId, 'items'],
    queryFn: async () => {
      const res = await fetchCategoryItems(toValue(categoryId))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดรายการไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateCategoryItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ categoryId, data }: { categoryId: number; data: CreateCategoryItem }) => {
      const res = await createCategoryItem(categoryId, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถเพิ่มรายการได้')
      return res.data
    },
    onSuccess: (_d, { categoryId }) => qc.invalidateQueries({ queryKey: itemsKey(categoryId) }),
  })
}

export function useUpdateCategoryItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({
      categoryId,
      itemId,
      data,
    }: {
      categoryId: number
      itemId: number
      data: UpdateCategoryItem
    }) => {
      const res = await updateCategoryItem(categoryId, itemId, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขรายการได้')
      return res.data
    },
    onSuccess: (_d, { categoryId }) => qc.invalidateQueries({ queryKey: itemsKey(categoryId) }),
  })
}

export function useDeleteCategoryItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ categoryId, itemId }: { categoryId: number; itemId: number }) => {
      const res = await deleteCategoryItem(categoryId, itemId)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบรายการได้')
    },
    onSuccess: (_d, { categoryId }) => qc.invalidateQueries({ queryKey: itemsKey(categoryId) }),
  })
}

export function useRestoreCategoryItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ categoryId, itemId }: { categoryId: number; itemId: number }) => {
      const res = await restoreCategoryItem(categoryId, itemId)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถกู้คืนรายการได้')
      return res.data
    },
    onSuccess: (_d, { categoryId }) => qc.invalidateQueries({ queryKey: itemsKey(categoryId) }),
  })
}

import { apiFetch } from '@/composables/useApi'
import type { ApiResponse } from '@/types/api'
import type {
  BudgetCategory,
  BudgetCategoryItem,
  CreateCategory,
  UpdateCategory,
  CreateCategoryItem,
  UpdateCategoryItem,
} from '@/types/budget-category'

// --- Categories ---

export async function fetchCategories(page = 1, perPage = 100): Promise<ApiResponse<BudgetCategory[]>> {
  return apiFetch<BudgetCategory[]>(`/categories?page=${page}&per_page=${perPage}`)
}

export async function fetchCategoryTree(): Promise<ApiResponse<BudgetCategory[]>> {
  return apiFetch<BudgetCategory[]>('/categories/tree')
}

export async function fetchCategoryById(id: number): Promise<ApiResponse<BudgetCategory>> {
  return apiFetch<BudgetCategory>(`/categories/${id}`)
}

export async function createCategory(data: CreateCategory): Promise<ApiResponse<BudgetCategory>> {
  return apiFetch<BudgetCategory>('/categories', { method: 'POST', body: JSON.stringify(data) })
}

export async function updateCategory(id: number, data: UpdateCategory): Promise<ApiResponse<BudgetCategory>> {
  return apiFetch<BudgetCategory>(`/categories/${id}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteCategory(id: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/categories/${id}`, { method: 'DELETE' })
}

// --- Category Items ---

export async function fetchCategoryItems(categoryId: number): Promise<ApiResponse<BudgetCategoryItem[]>> {
  return apiFetch<BudgetCategoryItem[]>(`/categories/${categoryId}/items`)
}

export async function createCategoryItem(categoryId: number, data: CreateCategoryItem): Promise<ApiResponse<BudgetCategoryItem[]>> {
  return apiFetch<BudgetCategoryItem[]>(`/categories/${categoryId}/items`, { method: 'POST', body: JSON.stringify(data) })
}

export async function updateCategoryItem(categoryId: number, itemId: number, data: UpdateCategoryItem): Promise<ApiResponse<BudgetCategoryItem[]>> {
  return apiFetch<BudgetCategoryItem[]>(`/categories/${categoryId}/items/${itemId}`, { method: 'PUT', body: JSON.stringify(data) })
}

export async function deleteCategoryItem(categoryId: number, itemId: number): Promise<ApiResponse<void>> {
  return apiFetch<void>(`/categories/${categoryId}/items/${itemId}`, { method: 'DELETE' })
}

export async function restoreCategoryItem(categoryId: number, itemId: number): Promise<ApiResponse<BudgetCategoryItem[]>> {
  return apiFetch<BudgetCategoryItem[]>(`/categories/${categoryId}/items/${itemId}/restore`, { method: 'POST' })
}

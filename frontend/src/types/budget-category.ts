export interface BudgetCategory {
  id: number
  code: string
  name_th: string
  name_en: string | null
  description: string | null
  parent_id: number | null
  level: number
  sort_order: number | null
  is_active: number | null
  created_at: string
  updated_at: string
  is_plan: number | null
  plan_name: string | null
  items?: BudgetCategoryItem[]
}

export interface BudgetCategoryItem {
  id: number
  category_id: number | null
  name: string
  code: string | null
  parent_id: number | null
  level: number
  sort_order: number
  is_active: number
  description: string | null
  deleted_at: string | null
  created_at: string
  updated_at: string
}

export interface CreateCategory {
  code: string
  name_th: string
  name_en?: string
  description?: string
  parent_id?: number | null
  sort_order?: number
  is_active?: boolean
}

export interface UpdateCategory {
  code?: string
  name_th?: string
  name_en?: string
  description?: string
  parent_id?: number | null
  sort_order?: number
  is_active?: boolean
}

export interface CreateCategoryItem {
  name: string
  code?: string
  parent_id?: number | null
  level?: number
  sort_order?: number
  is_active?: boolean
  description?: string
}

export interface UpdateCategoryItem {
  name?: string
  code?: string
  parent_id?: number | null
  level?: number
  sort_order?: number
  is_active?: boolean
  description?: string
}

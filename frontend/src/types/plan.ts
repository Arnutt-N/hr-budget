export interface Plan {
  id: number
  code: string | null
  name_th: string
  name_en: string | null
  description: string | null
  fiscal_year: number
  budget_type_id: number | null
  sort_order: number | null
  is_active: number | null
  deleted_at: string | null
  created_at: string
  updated_at: string
}

export interface CreatePlan {
  code?: string
  name_th: string
  name_en?: string
  description?: string
  fiscal_year: number
  budget_type_id?: number | null
  sort_order?: number
  is_active?: boolean
}

export interface UpdatePlan {
  code?: string
  name_th?: string
  name_en?: string
  description?: string
  fiscal_year?: number
  sort_order?: number
  is_active?: boolean
}

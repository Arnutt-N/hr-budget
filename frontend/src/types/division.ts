export interface Division {
  id: number
  code: string
  name_th: string
  name_en: string | null
  short_name: string | null
  parent_id: number | null
  type: string
  is_active: number | null
  sort_order: number | null
  created_at: string
  updated_at: string
}

export interface CreateDivision {
  code: string
  name_th: string
  name_en?: string
  short_name?: string
  type?: string
  parent_id?: number | null
  is_active?: boolean
}

export interface UpdateDivision {
  code?: string
  name_th?: string
  name_en?: string
  short_name?: string
  type?: string
  parent_id?: number | null
  is_active?: boolean
}

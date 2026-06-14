export interface TargetType {
  id: number
  code: string
  name_th: string
  description: string | null
  is_active: number | null
  sort_order: number | null
  created_at: string
  updated_at: string
}

export interface CreateTargetType {
  code: string
  name_th: string
  description?: string
  is_active?: boolean
}

export interface UpdateTargetType {
  code?: string
  name_th?: string
  description?: string
  is_active?: boolean
}

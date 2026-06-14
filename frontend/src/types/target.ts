export interface Target {
  id: number
  target_type_id: number
  fiscal_year: number
  quarter: number | null
  organization_id: number | null
  category_id: number | null
  target_percent: string | null
  target_amount: string | null
  notes: string | null
  created_by: number | null
  created_at: string
  updated_at: string
}

export interface CreateTarget {
  target_type_id: number
  fiscal_year: number
  quarter?: number | null
  organization_id?: number | null
  category_id?: number | null
  target_percent?: number | null
  target_amount?: number | null
  notes?: string
}

export interface UpdateTarget {
  target_type_id?: number
  fiscal_year?: number
  quarter?: number | null
  organization_id?: number | null
  category_id?: number | null
  target_percent?: number | null
  target_amount?: number | null
  notes?: string
}

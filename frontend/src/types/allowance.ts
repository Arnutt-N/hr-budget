export interface AllowanceType {
  id: number
  code: string
  name_th: string
  short_name: string | null
  expense_item_id: number | null
  expense_item_name: string | null
  scope: 'position' | 'personal'
  vacant_eligible: number
  report_scope: string
  basis: 'flat' | 'percent_of_salary' | 'by_level' | 'derived'
  rate_kind: 'exact' | 'ceiling'
  budget_basis: 'establishment' | 'actuals' | 'manual'
  legal_ref: string | null
  is_active: number
}

export interface UpdateAllowanceType {
  name_th?: string
  short_name?: string
  vacant_eligible?: boolean
  report_scope?: string[]
  budget_basis?: 'establishment' | 'actuals' | 'manual'
  legal_ref?: string
  is_active?: boolean
}

export interface AllowanceRate {
  id: number
  allowance_type_id: number
  level_code: string | null
  line_code: string | null
  amount: number | null
  percent: number | null
  derives_from_type_id: number | null
  derives_from_short_name: string | null
  fallback_amount: number | null
  effective_from: string
  effective_to: string | null
  doc_no: string | null
}

export interface CreateAllowanceRate {
  level_code?: string | null
  line_code?: string | null
  amount?: number | null
  percent?: number | null
  derives_from_type_id?: number | null
  fallback_amount?: number | null
  effective_from: string
  effective_to?: string | null
  doc_no?: string | null
}

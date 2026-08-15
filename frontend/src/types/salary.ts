import type { EmployeeCategory } from './position'

export interface SalaryScale {
  id: number
  employee_category: EmployeeCategory
  level_code: string
  min_amount: number
  max_amount: number
  effective_from: string
  effective_to: string | null
  doc_no: string | null
}

export interface CreateSalaryScale {
  employee_category: EmployeeCategory
  level_code: string
  min_amount: number
  max_amount: number
  effective_from: string
  effective_to?: string | null
  doc_no?: string | null
}

export interface SalaryRaiseRound {
  id: number
  round_month: 'apr' | 'oct'
  round_year_be: number
  effective_date: string
  fiscal_year_id: number
  fiscal_year: number | null
  include_in_budget: number
  is_active: number
}

export interface SalaryRaiseProgress {
  id: number
  round_id: number
  organization_id: number
  organization_name: string | null
  status: 'completed' | 'pending'
  completed_at: string | null
  doc_no: string | null
}

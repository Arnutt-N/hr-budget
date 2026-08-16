export interface PositionAllowance {
  id: number
  position_id: number
  allowance_type_id: number
  short_name: string | null
  allowance_name: string | null
  expense_item_id: number | null
  effective_from: string
  effective_to: string | null
  doc_no: string | null
}

export interface VacancyRecruitment {
  id: number
  position_id: number
  fiscal_year_id: number
  type: 'transfer_request' | 'eligibility_list' | 'ready_to_fill'
  doc_no: string | null
  doc_date: string | null
  pay_no: string | null
  pos_no: string | null
  organization_name: string | null
}

export interface CreateVacancyRecruitment {
  position_id: number
  fiscal_year_id: number
  type: VacancyRecruitment['type']
  doc_no?: string | null
  doc_date?: string | null
}

export interface PersonnelBudgetPolicy {
  id: number
  fiscal_year_id: number
  fiscal_year: number | null
  vacancy_rule: VacancyRecruitment['type'] | null
  calc_mode: 'snapshot' | 'prorate'
  buffer_percent: number | null
  reference_date: string | null
}

export interface CreatePersonnelBudgetPolicy {
  fiscal_year_id: number
  vacancy_rule?: VacancyRecruitment['type'] | null
  calc_mode: 'snapshot' | 'prorate'
  buffer_percent?: number | null
  reference_date?: string | null
}

export interface PersonnelAllowance {
  id: number
  person_id: string
  position_id: number
  allowance_type_id: number
  amount: number
  effective_from: string
  effective_to: string | null
  doc_no: string | null
  doc_date: string | null
  short_name: string | null
  allowance_name: string | null
  pay_no: string | null
}

export interface CreatePersonnelAllowance {
  person_id: string
  position_id: number
  allowance_type_id: number
  amount: number
  effective_from: string
  effective_to?: string | null
  doc_no?: string | null
  doc_date?: string | null
}

export interface PersonnelAssignment {
  id: number
  person_id: string
  position_id: number
  serving_organization_id: number
  effective_from: string
  effective_to: string | null
  doc_no: string | null
  doc_date: string | null
  pay_no: string | null
  serving_organization_name: string | null
}

export interface CreatePersonnelAssignment {
  person_id: string
  position_id: number
  serving_organization_id: number
  effective_from: string
  effective_to?: string | null
  doc_no?: string | null
  doc_date?: string | null
}

export interface ComputeBudgetResult {
  lines: Record<string, number>
  written?: number
  dry_run?: boolean
  message?: string
}

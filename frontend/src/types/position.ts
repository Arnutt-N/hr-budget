export type EmployeeCategory = 'civil_servant' | 'government_employee' | 'permanent_employee'
export type Occupancy = 'occupied' | 'vacant_funded' | 'vacant_unfunded'

export interface Position {
  id: number
  pay_no: string
  employee_category: EmployeeCategory
  created_doc_no: string | null
  is_active: number
  // current version (joined, effective as of today)
  version_id: number | null
  pos_no: string | null
  organization_id: number | null
  organization_name: string | null
  level_code: string | null
  line_code: string | null
  base_salary: number | null
  salary_basis: 'actual' | 'estimated' | null
  occupancy: Occupancy | null
  lifecycle: 'active' | 'abolished' | null
  months_counted: number | null
  approval_status: 'approved' | 'requested' | null
  effective_from: string | null
  effective_to: string | null
}

export interface PositionVersion {
  id: number
  position_id: number
  organization_id: number
  pos_no: string | null
  level_code: string | null
  line_code: string | null
  base_salary: number
  salary_basis: 'actual' | 'estimated'
  salary_pre_raise: number | null
  occupancy: Occupancy
  lifecycle: 'active' | 'abolished'
  months_counted: number
  approval_status: 'approved' | 'requested'
  effective_from: string
  effective_to: string | null
  order_doc_no: string | null
  order_doc_date: string | null
}

export interface CreatePosition {
  pay_no: string
  employee_category: EmployeeCategory
  created_doc_no?: string | null
  organization_id: number
  pos_no?: string | null
  level_code?: string | null
  line_code?: string | null
  base_salary: number
  occupancy: Occupancy
  months_counted: number
  effective_from: string
  order_doc_no?: string | null
  order_doc_date?: string | null
}

export interface UpdatePosition {
  pay_no?: string
  employee_category?: EmployeeCategory
  created_doc_no?: string | null
}

export interface CreatePositionVersion {
  organization_id: number
  pos_no?: string | null
  level_code?: string | null
  line_code?: string | null
  base_salary: number
  salary_basis: 'actual' | 'estimated'
  salary_pre_raise?: number | null
  occupancy: Occupancy
  lifecycle: 'active' | 'abolished'
  months_counted: number
  approval_status: 'approved' | 'requested'
  effective_from: string
  effective_to?: string | null
  order_doc_no?: string | null
  order_doc_date?: string | null
}

export type RequestStatus =
  | 'draft'
  | 'saved'
  | 'confirmed'
  | 'pending'
  | 'approved'
  | 'rejected'

export interface BudgetRequest {
  id: number
  fiscal_year: number
  request_title: string
  request_status: RequestStatus
  total_amount: string | null
  created_by: number
  org_id: number | null
  created_by_name?: string
  org_name?: string
  created_at: string
  updated_at: string
  submitted_at: string | null
  approved_at: string | null
  rejected_at: string | null
  rejected_reason: string | null
  items?: BudgetRequestItem[]
  approvals?: ApprovalLog[]
}

export interface BudgetRequestItem {
  id?: number
  budget_request_id?: number
  item_name: string
  quantity: string
  unit_price: string
  amount: string
  remark: string | null
  category_item_id: number | null
}

export interface ApprovalLog {
  id: number
  budget_request_id: number
  action: string
  user_id: number
  user_name?: string
  note: string | null
  created_at: string
}

export interface CreateBudgetRequest {
  request_title: string
  fiscal_year: number
  org_id?: number | null
  items: Omit<BudgetRequestItem, 'id' | 'budget_request_id' | 'amount'>[]
}

export interface UpdateBudgetRequest {
  request_title?: string
  fiscal_year?: number
  org_id?: number | null
  items?: Omit<BudgetRequestItem, 'id' | 'budget_request_id' | 'amount'>[]
}

export interface ListFilters {
  status?: RequestStatus
  fiscal_year?: number
  search?: string
  date_from?: string
  date_to?: string
  page?: number
  per_page?: number
}

export interface ListMeta {
  total: number
  page: number
  per_page: number
  total_pages: number
}

export const STATUS_LABELS: Record<RequestStatus, string> = {
  draft: 'ร่าง',
  saved: 'บันทึกแล้ว',
  confirmed: 'ยืนยันแล้ว',
  pending: 'รออนุมัติ',
  approved: 'อนุมัติแล้ว',
  rejected: 'ปฏิเสธ',
}

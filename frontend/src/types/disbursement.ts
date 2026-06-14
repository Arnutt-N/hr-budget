/**
 * Disbursement tracking contract types — mirror the PHP API JSON
 * (`/api/v1/disbursement-*` + `/api/v1/expense-structure`).
 * snake_case field names match the backend exactly. Decimal money columns
 * (allocated/transfer/disbursed/pending/po, remaining) arrive as strings.
 */

export type RecordStatus = 'draft' | 'completed'

/** A monthly tracking session scoped to org + fiscal year + month. */
export interface DisbursementSession {
  id: number
  organization_id: number
  fiscal_year: number
  record_month: number
  record_date: string
  created_by: number | null
  created_at: string
  updated_at: string
  /** Joined from organizations.name_th. */
  org_name?: string
}

/** One selectable activity for a session, with its (possibly existing) record. */
export interface Activity {
  activity_id: number
  code: string | null
  name_th: string
  plan_name?: string | null
  project_name?: string | null
  /** Existing record for this (session, activity), if any. */
  record_id: number | null
  record_status: RecordStatus | null
}

/** A per-activity record header (status transitions draft → completed). */
export interface DisbursementRecord {
  id: number
  session_id: number
  activity_id: number
  status: RecordStatus
  created_at: string
  updated_at: string
}

/** One amount row, keyed in detail responses by expense_item_id. */
export interface TrackingAmount {
  expense_item_id: number
  allocated: string
  transfer: string
  disbursed: string
  pending: string
  po: string
  /** Server-computed: allocated + transfer - (disbursed + pending + po). */
  remaining: string
}

/** Record detail = the record header + its session/activity + existing amounts. */
export interface DisbursementRecordDetail {
  record: DisbursementRecord
  session: DisbursementSession
  activity: Activity
  /** Existing tracking amounts keyed by expense_item_id (string keys in JSON). */
  trackings: Record<number, TrackingAmount>
}

/** A leaf expense item rendered as one numeric input row. */
export interface ExpenseItem {
  id: number
  expense_group_id: number | null
  expense_type_id: number | null
  code: string | null
  name_th: string
  level: number
  is_header: number
  is_active: number
}

/** A group node grouping its leaf items under a type. */
export interface ExpenseGroup {
  id: number
  expense_type_id: number
  code: string | null
  name_th: string
  items: ExpenseItem[]
}

/** Top-level reference tree node: type → groups → items. */
export interface ExpenseType {
  id: number
  code: string | null
  name_th: string
  groups: ExpenseGroup[]
}

/** Alias for a tree node (the plan refers to "ExpenseNode"). */
export type ExpenseNode = ExpenseType

// ---- Request bodies ----

export interface CreateSessionBody {
  organization_id: number
  fiscal_year: number
  record_month: number
  record_date?: string
}

export interface CreateRecordBody {
  session_id: number
  activity_id: number
}

/** One item row in a save request (amounts as numeric strings). */
export interface SaveTrackingItem {
  expense_item_id: number
  allocated: string
  transfer: string
  disbursed: string
  pending: string
  po: string
}

export interface SaveRecordBody {
  items: SaveTrackingItem[]
}

// ---- List filters + pagination meta ----

export interface SessionFilters {
  fiscal_year?: number
  organization_id?: number
  record_month?: number
  page?: number
  per_page?: number
}

export interface SessionListMeta {
  total: number
  page: number
  per_page: number
  total_pages: number
}

/** เดือนงบประมาณ (ปฏิทินงบประมาณเริ่ม ต.ค. = เดือน 1 ในที่นี้ใช้เลขเดือนจริง 1-12). */
export const MONTH_LABELS: Record<number, string> = {
  1: 'มกราคม',
  2: 'กุมภาพันธ์',
  3: 'มีนาคม',
  4: 'เมษายน',
  5: 'พฤษภาคม',
  6: 'มิถุนายน',
  7: 'กรกฎาคม',
  8: 'สิงหาคม',
  9: 'กันยายน',
  10: 'ตุลาคม',
  11: 'พฤศจิกายน',
  12: 'ธันวาคม',
}

export const RECORD_STATUS_LABELS: Record<RecordStatus, string> = {
  draft: 'ร่าง',
  completed: 'บันทึกแล้ว',
}

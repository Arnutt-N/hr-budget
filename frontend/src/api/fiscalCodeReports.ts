import { apiFetch } from '@/composables/useApi'
import type {
  AgencyRow,
  ActivityRow,
  ExpenseCategoryRow,
  FiscalCodeRow,
  FiscalCodeRowSet,
  ParentRollupRow,
} from '@/types/fiscal-code-report'

/** Raw JSON row. Every field is unknown until the boundary narrows it. */
interface WireFiscalCodeRow {
  code?: unknown
  activity_code?: unknown
  parent_code?: unknown
  agency_code?: unknown
  name_th?: unknown
  count?: unknown
  allocated_pba?: unknown
}

export interface FiscalCodeFetchOptions {
  path: string
  hasAllocation: boolean
  year?: number
}

const INVALID_ROW = 'ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง'

function firstStringCode(raw: WireFiscalCodeRow): string {
  for (const key of ['code', 'activity_code', 'parent_code', 'agency_code'] as const) {
    if (typeof raw[key] === 'string' && raw[key] !== '') return raw[key] as string
  }
  throw new Error(INVALID_ROW)
}

function toRow(raw: WireFiscalCodeRow, hasAllocation: boolean): FiscalCodeRow {
  const code = firstStringCode(raw)
  const name_th = typeof raw.name_th === 'string' ? raw.name_th : null
  if (typeof raw.count !== 'number' || !Number.isFinite(raw.count)) {
    throw new Error(INVALID_ROW)
  }

  if (hasAllocation) {
    const amount = typeof raw.allocated_pba === 'number' && Number.isFinite(raw.allocated_pba)
      ? raw.allocated_pba
      : 0
    return { code, name_th, count: raw.count, allocated_pba: amount } as ExpenseCategoryRow
  }
  return { code, name_th, count: raw.count } as ActivityRow | ParentRollupRow | AgencyRow
}

/** Fetch and normalize one fiscal-code report. Throws Thai errors at the boundary. */
export async function fetchFiscalCodeReport(options: FiscalCodeFetchOptions): Promise<FiscalCodeRowSet> {
  const params = new URLSearchParams()
  if (options.year) params.set('year', String(options.year))
  const qs = params.toString()
  const path = `${options.path}${qs ? `?${qs}` : ''}`

  const res = await apiFetch<unknown>(path)
  if (!res.success) throw new Error(res.error ?? 'โหลดรายงานรหัสงบประมาณไม่สำเร็จ')
  if (!Array.isArray(res.data)) throw new Error(INVALID_ROW)

  return res.data.map((raw: WireFiscalCodeRow) => toRow(raw, options.hasAllocation))
}

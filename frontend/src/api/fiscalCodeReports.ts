import { apiFetch } from '@/composables/useApi'
import type {
  ExpenseCategoryRow,
  FiscalCodeCountRow,
  FiscalCodeRow,
  FiscalCodeRowSet,
} from '@/types/fiscal-code-report'

interface WireFiscalCodeRow {
  code?: unknown
  activity_code?: unknown
  parent_code?: unknown
  agency_code?: unknown
  name_th?: unknown
  count?: unknown
  allocated_pba?: unknown
}

export interface FiscalCodeYearOption {
  year: number
  is_current: boolean
}

const INVALID_YEAR_PAYLOAD = 'ข้อมูลปีงบประมาณไม่ถูกต้อง'

export async function fetchFiscalCodeReportYears(): Promise<FiscalCodeYearOption[]> {
  const res = await apiFetch<unknown>('/reports/fiscal-codes/years')
  if (!res.success) throw new Error(res.error ?? 'โหลดปีงบประมาณไม่สำเร็จ')
  if (!Array.isArray(res.data)) throw new Error(INVALID_YEAR_PAYLOAD)

  return res.data.map((raw: unknown) => {
    if (typeof raw !== 'object' || raw === null) throw new Error(INVALID_YEAR_PAYLOAD)
    const year = (raw as { year?: unknown }).year
    if (typeof year !== 'number' || !Number.isFinite(year)) throw new Error(INVALID_YEAR_PAYLOAD)
    return { year, is_current: (raw as { is_current?: unknown }).is_current === true }
  })
}

export interface FiscalCodeFetchOptions {
  path: string
  hasAllocation: boolean
  year?: number
}

const INVALID_ROW = 'ข้อมูลรายงานรหัสงบประมาณไม่ถูกต้อง'

function finiteNumber(value: unknown): number | null {
  if (typeof value === 'number' && Number.isFinite(value)) return value
  if (typeof value === 'string' && value.trim() !== '') {
    const parsed = Number(value)
    if (Number.isFinite(parsed)) return parsed
  }
  return null
}

function firstStringCode(raw: WireFiscalCodeRow): string {
  for (const key of ['code', 'activity_code', 'parent_code', 'agency_code'] as const) {
    if (typeof raw[key] === 'string' && raw[key] !== '') return raw[key] as string
  }
  throw new Error(INVALID_ROW)
}

function toRow(raw: WireFiscalCodeRow, hasAllocation: boolean): FiscalCodeRow {
  const code = firstStringCode(raw)
  const name_th = typeof raw.name_th === 'string' ? raw.name_th : null
  const count = finiteNumber(raw.count)
  if (count === null) {
    throw new Error(INVALID_ROW)
  }

  if (hasAllocation) {
    const amount = finiteNumber(raw.allocated_pba)
    if (amount === null) {
      throw new Error(INVALID_ROW)
    }
    return { code, name_th, count, allocated_pba: amount } as ExpenseCategoryRow
  }
  return { code, name_th, count } satisfies FiscalCodeCountRow
}

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

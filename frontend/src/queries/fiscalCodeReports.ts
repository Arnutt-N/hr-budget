import { useQuery, type UseQueryReturnType } from '@tanstack/vue-query'
import { computed, type ComputedRef, type Ref } from 'vue'
import { fetchFiscalCodeReport, fetchFiscalCodeReportYears } from '@/api/fiscalCodeReports'
import type { FiscalCodeYearOption } from '@/api/fiscalCodeReports'
import type { FiscalCodeReportId, FiscalCodeRowSet } from '@/types/fiscal-code-report'

const QUERY_KEY = ['fiscal-code-reports'] as const
const YEARS_QUERY_KEY = ['fiscal-code-report-years'] as const
type QueryResult = UseQueryReturnType<FiscalCodeRowSet, Error>

export function useFiscalCodeReportYears() {
  return useQuery<FiscalCodeYearOption[], Error>({
    queryKey: YEARS_QUERY_KEY,
    queryFn: fetchFiscalCodeReportYears,
  })
}

interface ReportMeta {
  id: FiscalCodeReportId
  path: string
  title: string
  scope: string
  supportsYear: boolean
  hasAllocation: boolean
}

const REPORTS: readonly ReportMeta[] = [
  {
    id: 'by-expense-category',
    path: '/reports/fiscal-codes/by-expense-category',
    title: 'รหัสตามประเภทรายจ่าย',
    scope: 'ปีที่เลือกใช้เฉพาะยอดจัดสรร จำนวนรหัสไม่กรองตามปี',
    supportsYear: true,
    hasAllocation: true,
  },
  {
    id: 'by-activity',
    path: '/reports/fiscal-codes/by-activity',
    title: 'รหัสตามกิจกรรมหลัก',
    scope: 'จำนวนแถวแผนในปีที่เลือก',
    supportsYear: true,
    hasAllocation: false,
  },
  {
    id: 'by-parent-rollup',
    path: '/reports/fiscal-codes/by-parent-rollup',
    title: 'รหัสลูกต่อรหัสแม่',
    scope: 'ไม่กรองตามปี แสดงข้อมูลรหัสทั้งหมดในระบบ',
    supportsYear: false,
    hasAllocation: false,
  },
  {
    id: 'by-agency',
    path: '/reports/fiscal-codes/by-agency',
    title: 'รหัสลูกตามหน่วยงาน',
    scope: 'ไม่กรองตามปี แสดงข้อมูลรหัสทั้งหมดในระบบ',
    supportsYear: false,
    hasAllocation: false,
  },
]

function buildQuery(meta: ReportMeta, year: Ref<number | undefined>) {
  const effectiveYear = computed(() => (meta.supportsYear ? year.value : undefined))
  return {
    queryKey: computed(() => [
      ...QUERY_KEY,
      meta.id,
      ...(meta.supportsYear ? [effectiveYear.value] : []),
    ]),
    queryFn: async () =>
      fetchFiscalCodeReport({
        path: meta.path,
        hasAllocation: meta.hasAllocation,
        year: effectiveYear.value,
      }),
    enabled: computed(() => !meta.supportsYear || !!year.value),
  }
}

export interface FiscalCodeSection {
  title: string
  scope: string
  supportsYear: boolean
  hasAllocation: boolean
  rows: FiscalCodeRowSet
  isLoading: boolean
  error: Error | null
  retry: () => void
}

export function useFiscalCodeReports(year: Ref<number | undefined>): {
  sections: ComputedRef<readonly FiscalCodeSection[]>
} {
  const expenseCategory = useQuery<FiscalCodeRowSet, Error>(buildQuery(REPORTS[0], year))
  const activity = useQuery<FiscalCodeRowSet, Error>(buildQuery(REPORTS[1], year))
  const parentRollup = useQuery<FiscalCodeRowSet, Error>(buildQuery(REPORTS[2], year))
  const agency = useQuery<FiscalCodeRowSet, Error>(buildQuery(REPORTS[3], year))

  const queries: Record<FiscalCodeReportId, QueryResult> = {
    'by-expense-category': expenseCategory,
    'by-activity': activity,
    'by-parent-rollup': parentRollup,
    'by-agency': agency,
  }

  const sections = computed<readonly FiscalCodeSection[]>(() =>
    REPORTS.map((meta) => {
      const query = queries[meta.id]
      return {
        title: meta.title,
        scope: meta.scope,
        supportsYear: meta.supportsYear,
        hasAllocation: meta.hasAllocation,
        rows: query.data.value ?? [],
        isLoading: query.isLoading.value,
        error: query.error.value ?? null,
        retry: () => {
          void query.refetch()
        },
      }
    }),
  )

  return { sections }
}

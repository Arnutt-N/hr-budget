export type FiscalCodeReportId =
  | 'by-expense-category'
  | 'by-activity'
  | 'by-parent-rollup'
  | 'by-agency'

export interface FiscalCodeCountRow {
  code: string
  name_th: string | null
  count: number
}

export interface ExpenseCategoryRow extends FiscalCodeCountRow {
  allocated_pba: number
}

export type FiscalCodeRow = FiscalCodeCountRow | ExpenseCategoryRow

export type FiscalCodeRowSet = FiscalCodeRow[]

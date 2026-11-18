/** One entry per fiscal-code report endpoint. */
export type FiscalCodeReportId =
  | 'by-expense-category'
  | 'by-activity'
  | 'by-parent-rollup'
  | 'by-agency'

/** Shape shared by every report: a code, an optional Thai name, and a count. */
export interface FiscalCodeCountRow {
  code: string
  name_th: string | null
  count: number
}

export interface ExpenseCategoryRow extends FiscalCodeCountRow {
  allocated_pba: number
}

export interface ActivityRow extends FiscalCodeCountRow {}

export interface ParentRollupRow extends FiscalCodeCountRow {}

export interface AgencyRow extends FiscalCodeCountRow {}

export type FiscalCodeRow =
  | ExpenseCategoryRow
  | ActivityRow
  | ParentRollupRow
  | AgencyRow

export type FiscalCodeRowSet = FiscalCodeRow[]

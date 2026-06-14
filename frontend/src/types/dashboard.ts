/** Aggregated budget figures for one fiscal year (Buddhist era). */
export interface DashboardSummary {
  fiscal_year: number
  allocated: number
  transfer: number
  total_budget: number
  disbursed: number
  pending: number
  po: number
  total_used: number
  remaining: number
  used_percent: number
}

/** 12-month expenditure series, fiscal-year order (Oct → Sep). */
export interface MonthlyChart {
  labels: string[]
  data: number[]
}

/**
 * Analytics response types — mirror the PHP `AnalyticsService` array shapes
 * (snake_case fields, Buddhist-era years, money as float).
 * See PRP 2026-06-19 §4 + the Phase-1 backend response contracts.
 */

/** Org-scope marker the backend stamps so the UI can show a "scoped" disclaimer. */
export type AnalyticsScope = 'all' | 'subtree'

/** A comparison toggle dimension — must match the backend whitelist. */
export type ComparisonDimension = 'year' | 'quarter' | 'month'

/** One comparison row: a year, quarter, or month bucket of budget vs disbursed. */
export interface ComparisonRow {
  /** Present only when dimension=year (Buddhist era). */
  fiscal_year?: number
  /** Present for quarter/month dimensions (Thai label). */
  label?: string
  budget: number
  disbursed: number
}

export interface ComparisonReport {
  dimension: ComparisonDimension
  scope: AnalyticsScope
  rows: ComparisonRow[]
}

/** Forecast vs actual — 12-month aligned arrays (Oct → Sep). */
export interface ForecastReport {
  scope: AnalyticsScope
  total_allocated: number
  labels: string[]
  forecast_monthly: number[]
  actual_monthly: number[]
  forecast_cumulative: number[]
  actual_cumulative: number[]
}

/** Per-organization requested-vs-approved breakdown row. */
export interface RequestApprovalOrgRow {
  org_name: string
  requested: number
  approved: number
}

export interface RequestApprovalReport {
  scope: AnalyticsScope
  fiscal_year: number
  requested: number
  approved: number
  /** Ratio approved ÷ requested, 0–1 (the UI formats it as a %). */
  approval_rate: number
  by_org: RequestApprovalOrgRow[]
}

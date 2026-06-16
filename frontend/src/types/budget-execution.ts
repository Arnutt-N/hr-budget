/** Money + quarter figures shared by execution projects and activities. */
export interface ExecutionMoney {
  allocated: number
  transfer: number
  disbursed: number
  po: number
  pending: number
  q1: number
  q2: number
  q3: number
  q4: number
  net_budget: number
  total_used: number
  balance: number
  used_percent: number
}

export interface ExecutionActivity extends ExecutionMoney {
  activity_id: number
  activity_name: string
}

export interface ExecutionProject extends ExecutionMoney {
  project_id: number
  project_name: string
  org_name: string
  activities: ExecutionActivity[]
}

/** Grand-total KPI figures for the fiscal year (Σ of all project rows). */
export interface ExecutionStats {
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

/** Ranked name → amount series for a chart. */
export interface RankedChart {
  labels: string[]
  values: number[]
}

export interface ExecutionReport {
  fiscal_year: number
  organization_id: number | null
  stats: ExecutionStats
  projects: ExecutionProject[]
  category_chart: RankedChart
  org_chart: RankedChart
}

export interface ExecutionYear {
  fiscal_year: number
}

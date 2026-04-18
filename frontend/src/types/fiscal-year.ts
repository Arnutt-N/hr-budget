export interface FiscalYear {
  id: number
  year: number
  start_date: string
  end_date: string
  is_current: number
  is_closed: number
  created_at: string
  updated_at: string
}

export interface CreateFiscalYear {
  year: number
  start_date: string
  end_date: string
  is_current?: boolean
}

export interface UpdateFiscalYear {
  year?: number
  start_date?: string
  end_date?: string
  is_current?: boolean
  is_closed?: boolean
}

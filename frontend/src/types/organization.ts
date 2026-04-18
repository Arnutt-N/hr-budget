export interface Organization {
  id: number
  parent_id: number | null
  code: string
  name_th: string
  abbreviation: string | null
  budget_allocated: string | null
  level: number
  org_type: string | null
  province_code: string | null
  region: string | null
  contact_phone: string | null
  contact_email: string | null
  address: string | null
  sort_order: number | null
  is_active: number | null
  created_at: string
  updated_at: string
}

export interface CreateOrganization {
  code: string
  name_th: string
  abbreviation?: string
  org_type?: string
  region?: string
  parent_id?: number | null
}

export interface UpdateOrganization {
  code?: string
  name_th?: string
  abbreviation?: string
  org_type?: string
  region?: string
  parent_id?: number | null
  is_active?: boolean
}

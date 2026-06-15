export interface VaultFolder {
  id: number
  name: string
  fiscal_year: number | null
  parent_id: number | null
  is_system: number | boolean
  folder_path?: string | null
  description?: string | null
  subfolder_count?: number
  file_count?: number
  created_by_name?: string | null
  created_at?: string
}

export interface VaultFile {
  id: number
  folder_id: number
  original_name: string
  file_type: string
  file_size: number
  mime_type?: string | null
  description?: string | null
  uploaded_by_name?: string | null
  created_at?: string
}

export interface Breadcrumb {
  id: number
  name: string
  parent_id?: number | null
  fiscal_year?: number | null
}

export interface FolderListResponse {
  folders: VaultFolder[]
  breadcrumb: Breadcrumb[]
}

export interface VaultYear {
  fiscal_year: number
}

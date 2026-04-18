export interface FileAttachment {
  id: number
  request_id: number
  original_name: string
  stored_name: string
  file_type: string
  file_size: number
  mime_type: string | null
  uploaded_by: number
  uploaded_by_name?: string
  created_at: string
}

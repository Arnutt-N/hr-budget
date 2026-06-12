export interface Notification {
  id: number
  user_id: number
  type: 'approval_request' | 'approved' | 'rejected' | string
  title: string
  message: string | null
  link: string | null
  is_read: boolean
  created_at: string
}

export interface UnreadCountResponse {
  unread_count: number
}

export interface User {
  id: number
  email: string
  name: string
  role: string
  is_active: number | null
  department: string | null
  created_at: string
  updated_at: string
  last_login_at: string | null
}

export interface CreateUser {
  email: string
  password: string
  name: string
  role?: string
  is_active?: boolean
  department?: string
}

export interface UpdateUser {
  email?: string
  password?: string
  name?: string
  role?: string
  is_active?: boolean
  department?: string
}

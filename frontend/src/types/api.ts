/**
 * Shared API contract types — mirror the PHP backend DTOs.
 * Keep snake_case field names matching backend (expires_in, is_active, etc.)
 */

export interface ApiResponse<T> {
  success: boolean
  data?: T
  error?: string
  details?: Record<string, string>
  meta?: Record<string, unknown>
}

export interface User {
  id: number
  email: string
  name: string
  role: string
}

export interface LoginRequest {
  email: string
  password: string
}

export interface AuthResponse {
  token: string
  expires_in: number
  user: User
}

export interface HealthResponse {
  version: string
  time: string
  env: string
}

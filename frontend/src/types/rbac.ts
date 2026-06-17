/**
 * RBAC + stepped-approval contract types — mirror the PHP backend shapes
 * (Phase 1 RBAC + Phase 4 approval chain). Field names stay snake_case to
 * match the JSON envelope. MySQL tinyint flags arrive as 0 | 1 numbers.
 */

export type ScopeType = 'organization' | 'all' | 'category' | 'region'

/** A role enriched with its permission codes (RoleService::list / findById). */
export interface Role {
  id: number
  code: string
  name_th: string
  name_en: string | null
  description: string | null
  is_system: number
  is_active: number
  sort_order: number
  permissions: string[]
}

/** The fixed permission catalogue (PermissionRepository::findAll). */
export interface Permission {
  id: number
  code: string
  name_th: string
  resource: string | null
}

/** A user's role-scope grant (AccessGrantRepository::findByUser). */
export interface AccessGrant {
  id: number
  user_id: number
  role_id: number
  scope_type: ScopeType
  scope_ref_id: number | null
  is_active: number
  role_code: string
  role_name_th: string
  created_at?: string | null
}

/** Body for POST /users/{id}/access-grants (AssignGrantDto). */
export interface AssignGrantPayload {
  role_id: number
  scope_type: ScopeType
  scope_ref_id?: number | null
}

/** Effective permissions + org scope of the current user (MeController). */
export interface MyPermissions {
  user_id: number
  is_super_admin: boolean
  has_all_orgs: boolean
  permissions: string[]
  organization_ids: number[]
}

/** A configured approval-chain level (approval_levels). */
export interface ApprovalLevel {
  id: number
  level: number
  code: string
  name_th: string
  role_code: string
  is_active: number
}

/** One recorded action in a request's approval history. */
export interface ApprovalHistoryItem {
  id: number
  budget_request_id: number
  action: string
  level: number | null
  user_id: number
  note: string | null
  created_at: string | null
}

/** Current chain state of a request (ApprovalChainService::status). */
export interface ApprovalStatus {
  request_status: string | null
  current_level: number | null
  history: ApprovalHistoryItem[]
}

/** Result of an approve/reject action on the chain. */
export interface ApprovalActionResult {
  request_status: string | null
  current_level: number | null
}

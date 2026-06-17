/**
 * Pure display helpers for RBAC + approval UI. No I/O — unit-tested in isolation.
 */
import type { ScopeType } from '@/types/rbac'

const SCOPE_LABELS: Record<ScopeType, string> = {
  organization: 'หน่วยงาน (และหน่วยงานลูก)',
  all: 'ทั้งหมด',
  category: 'หมวดงบ',
  region: 'ภูมิภาค',
}

/** Thai label for a grant's scope type; falls back to the raw value if unknown. */
export function scopeTypeLabel(scope: string): string {
  return SCOPE_LABELS[scope as ScopeType] ?? scope
}

/** PrimeVue Tag severity for an active flag (0/1 or boolean). */
export function activeSeverity(isActive: number | boolean): 'success' | 'secondary' {
  return isActive ? 'success' : 'secondary'
}

/** Thai label for an approval action verb. */
const APPROVAL_ACTIONS: Record<string, string> = {
  approved: 'อนุมัติ',
  rejected: 'ปฏิเสธ',
}
export function approvalActionLabel(action: string): string {
  return APPROVAL_ACTIONS[action] ?? action
}

/**
 * Whether the viewer may act on the chain right now: holds request.approve and
 * the request is pending + sitting on a level. The backend still enforces the
 * per-level role + scope — this only governs button visibility.
 */
export function canActOnChain(
  permissions: string[],
  status: { request_status: string | null; current_level: number | null } | null,
): boolean {
  if (!status) return false
  return (
    permissions.includes('request.approve') &&
    status.request_status === 'pending' &&
    status.current_level !== null
  )
}

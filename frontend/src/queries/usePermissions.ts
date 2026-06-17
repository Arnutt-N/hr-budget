import { useQuery } from '@tanstack/vue-query'
import { fetchMyPermissions, fetchPermissions } from '@/api/rbac'

/**
 * Effective permissions + org scope of the current user. Drives action/menu
 * visibility across the SPA. Cached for the session (rarely changes mid-session).
 */
export function useMyPermissions() {
  return useQuery({
    queryKey: ['me', 'permissions'] as const,
    queryFn: async () => {
      const res = await fetchMyPermissions()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดสิทธิ์ไม่สำเร็จ')
      return res.data
    },
    staleTime: 5 * 60 * 1000,
  })
}

/** The fixed permission catalogue (for role displays). */
export function usePermissionCatalogue() {
  return useQuery({
    queryKey: ['permissions'] as const,
    queryFn: async () => {
      const res = await fetchPermissions()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดรายการสิทธิ์ไม่สำเร็จ')
      return res.data
    },
    staleTime: 10 * 60 * 1000,
  })
}

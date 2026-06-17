import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { fetchRoles, updateRole } from '@/api/rbac'

const QUERY_KEY = ['roles'] as const

export function useRoleList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchRoles()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดบทบาทไม่สำเร็จ')
      return res.data
    },
  })
}

export function useUpdateRole() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number
      data: Partial<{ name_th: string; is_active: boolean; sort_order: number }>
    }) => {
      const res = await updateRole(id, data)
      if (!res.success) throw new Error(res.error ?? 'แก้ไขบทบาทไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { fetchRoles, createRole, updateRole, deleteRole } from '@/api/rbac'
import type { CreateRolePayload, UpdateRolePayload } from '@/types/rbac'

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

export function useCreateRole() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateRolePayload) => {
      const res = await createRole(data)
      if (!res.success) throw new Error(res.error ?? 'สร้างบทบาทไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateRole() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateRolePayload }) => {
      const res = await updateRole(id, data)
      if (!res.success) throw new Error(res.error ?? 'แก้ไขบทบาทไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteRole() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteRole(id)
      if (!res.success) throw new Error(res.error ?? 'ลบบทบาทไม่สำเร็จ')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

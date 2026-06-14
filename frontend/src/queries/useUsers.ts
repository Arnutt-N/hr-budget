import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateUser, UpdateUser } from '@/types/user'
import { fetchUsers, createUser, updateUser, deleteUser } from '@/api/users'

const QUERY_KEY = ['users'] as const

export function useUserList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchUsers()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateUser() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateUser) => {
      const res = await createUser(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างผู้ใช้ได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateUser() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateUser }) => {
      const res = await updateUser(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขผู้ใช้ได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteUser() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteUser(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบผู้ใช้ได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

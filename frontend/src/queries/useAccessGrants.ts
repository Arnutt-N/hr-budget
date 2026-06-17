import { computed, unref, type MaybeRef } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { fetchUserGrants, createGrant, deleteGrant } from '@/api/rbac'
import type { AssignGrantPayload } from '@/types/rbac'

const userGrantsKey = (userId: number) => ['users', userId, 'access-grants'] as const

export function useUserGrants(userId: MaybeRef<number>) {
  const id = computed(() => unref(userId))
  return useQuery({
    queryKey: computed(() => userGrantsKey(id.value)),
    queryFn: async () => {
      const res = await fetchUserGrants(id.value)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดสิทธิ์ผู้ใช้ไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => id.value > 0),
  })
}

export function useCreateGrant(userId: MaybeRef<number>) {
  const qc = useQueryClient()
  const id = computed(() => unref(userId))
  return useMutation({
    mutationFn: async (data: AssignGrantPayload) => {
      const res = await createGrant(id.value, data)
      if (!res.success) throw new Error(res.error ?? 'มอบสิทธิ์ไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: userGrantsKey(id.value) }),
  })
}

export function useDeleteGrant(userId: MaybeRef<number>) {
  const qc = useQueryClient()
  const id = computed(() => unref(userId))
  return useMutation({
    mutationFn: async (grantId: number) => {
      const res = await deleteGrant(grantId)
      if (!res.success) throw new Error(res.error ?? 'ถอนสิทธิ์ไม่สำเร็จ')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: userGrantsKey(id.value) }),
  })
}

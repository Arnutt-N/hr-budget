import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateTarget, UpdateTarget } from '@/types/target'
import { fetchTargets, createTarget, updateTarget, deleteTarget } from '@/api/targets'

const QUERY_KEY = ['targets'] as const

export function useTargetList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchTargets()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateTarget() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateTarget) => {
      const res = await createTarget(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างเป้าหมายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateTarget() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateTarget }) => {
      const res = await updateTarget(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขเป้าหมายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteTarget() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteTarget(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบเป้าหมายได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

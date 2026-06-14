import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreatePlan, UpdatePlan } from '@/types/plan'
import { fetchPlans, createPlan, updatePlan, deletePlan } from '@/api/plans'

const QUERY_KEY = ['plans'] as const

export function usePlanList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchPlans()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePlan() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreatePlan) => {
      const res = await createPlan(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างแผนงานได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdatePlan() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdatePlan }) => {
      const res = await updatePlan(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขแผนงานได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeletePlan() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deletePlan(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบแผนงานได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

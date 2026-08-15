import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { Ref } from 'vue'
import { computed } from 'vue'
import type { CreateSalaryScale } from '@/types/salary'
import {
  fetchSalaryScales,
  createSalaryScale,
  deleteSalaryScale,
  fetchSalaryRaiseRounds,
  setIncludeInBudget,
  fetchRaiseProgress,
  markRaiseProgress,
  seedRaiseProgress,
} from '@/api/salary'

export function useSalaryScaleList() {
  return useQuery({
    queryKey: ['salary-scales'],
    queryFn: async () => {
      const res = await fetchSalaryScales()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateSalaryScale() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateSalaryScale) => {
      const res = await createSalaryScale(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างอัตราเงินเดือนได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['salary-scales'] }),
  })
}

export function useDeleteSalaryScale() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteSalaryScale(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบอัตราเงินเดือนได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['salary-scales'] }),
  })
}

export function useSalaryRaiseRounds() {
  return useQuery({
    queryKey: ['salary-raise-rounds'],
    queryFn: async () => {
      const res = await fetchSalaryRaiseRounds()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useSetIncludeInBudget() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ roundId, include }: { roundId: number; include: boolean }) => {
      const res = await setIncludeInBudget(roundId, include)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถอัปเดตสวิตช์ได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['salary-raise-rounds'] }),
  })
}

export function useRaiseProgress(roundId: Ref<number | null>) {
  return useQuery({
    queryKey: computed(() => ['raise-progress', roundId.value]),
    enabled: computed(() => roundId.value !== null),
    queryFn: async () => {
      const res = await fetchRaiseProgress(roundId.value!)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดสถานะไม่สำเร็จ')
      return res.data
    },
  })
}

export function useMarkRaiseProgress() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({
      roundId,
      organizationId,
      status,
      docNo,
    }: {
      roundId: number
      organizationId: number
      status: 'completed' | 'pending'
      docNo?: string | null
    }) => {
      const res = await markRaiseProgress(roundId, organizationId, status, docNo)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถบันทึกสถานะได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['raise-progress'] }),
  })
}

export function useSeedRaiseProgress() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (roundId: number) => {
      const res = await seedRaiseProgress(roundId)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างแถวติดตามได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['raise-progress'] }),
  })
}

import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { Ref } from 'vue'
import { computed } from 'vue'
import type { UpdateAllowanceType, CreateAllowanceRate } from '@/types/allowance'
import {
  fetchAllowanceTypes,
  updateAllowanceType,
  fetchAllowanceRates,
  createAllowanceRate,
  deleteAllowanceRate,
} from '@/api/allowanceTypes'

const QUERY_KEY = ['allowance-types'] as const

export function useAllowanceTypeList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchAllowanceTypes()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useUpdateAllowanceType() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateAllowanceType }) => {
      const res = await updateAllowanceType(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขชนิดเงินเพิ่มได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useAllowanceRates(typeId: Ref<number | null>) {
  return useQuery({
    queryKey: computed(() => ['allowance-rates', typeId.value]),
    enabled: computed(() => typeId.value !== null),
    queryFn: async () => {
      const res = await fetchAllowanceRates(typeId.value!)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดอัตราไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateAllowanceRate() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ typeId, data }: { typeId: number; data: CreateAllowanceRate }) => {
      const res = await createAllowanceRate(typeId, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถบันทึกอัตราได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['allowance-rates'] }),
  })
}

export function useDeleteAllowanceRate() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ typeId, rateId }: { typeId: number; rateId: number }) => {
      const res = await deleteAllowanceRate(typeId, rateId)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบอัตราได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['allowance-rates'] }),
  })
}

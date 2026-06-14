import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateFiscalYear, UpdateFiscalYear } from '@/types/fiscal-year'
import {
  fetchFiscalYears,
  createFiscalYear,
  updateFiscalYear,
  deleteFiscalYear,
  setCurrentFiscalYear,
} from '@/api/fiscalYears'

const QUERY_KEY = ['fiscal-years'] as const

export function useFiscalYearList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchFiscalYears()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateFiscalYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateFiscalYear) => {
      const res = await createFiscalYear(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างปีงบประมาณได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateFiscalYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateFiscalYear }) => {
      const res = await updateFiscalYear(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขปีงบประมาณได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteFiscalYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteFiscalYear(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบปีงบประมาณได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useSetCurrentFiscalYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await setCurrentFiscalYear(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถตั้งเป็นปีปัจจุบันได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

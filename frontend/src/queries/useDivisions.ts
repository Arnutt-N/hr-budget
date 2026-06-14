import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateDivision, UpdateDivision } from '@/types/division'
import {
  fetchDivisions,
  createDivision,
  updateDivision,
  deleteDivision,
} from '@/api/divisions'

const QUERY_KEY = ['divisions'] as const

export function useDivisionList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchDivisions()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateDivision() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateDivision) => {
      const res = await createDivision(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างกอง/สำนักได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateDivision() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateDivision }) => {
      const res = await updateDivision(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขกอง/สำนักได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteDivision() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteDivision(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบกอง/สำนักได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

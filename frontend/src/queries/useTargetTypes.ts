import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateTargetType, UpdateTargetType } from '@/types/target-type'
import {
  fetchTargetTypes,
  createTargetType,
  updateTargetType,
  deleteTargetType,
} from '@/api/targetTypes'

const QUERY_KEY = ['target-types'] as const

export function useTargetTypeList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchTargetTypes()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateTargetType() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateTargetType) => {
      const res = await createTargetType(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างประเภทเป้าหมายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateTargetType() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateTargetType }) => {
      const res = await updateTargetType(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขประเภทเป้าหมายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteTargetType() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteTargetType(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบประเภทเป้าหมายได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

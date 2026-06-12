import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreateOrganization, UpdateOrganization } from '@/types/organization'
import {
  fetchOrganizations,
  createOrganization,
  updateOrganization,
  deleteOrganization,
} from '@/api/organizations'

const QUERY_KEY = ['organizations'] as const

export function useOrganizationList() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: async () => {
      const res = await fetchOrganizations()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateOrganization() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateOrganization) => {
      const res = await createOrganization(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างหน่วยงานได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdateOrganization() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdateOrganization }) => {
      const res = await updateOrganization(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขหน่วยงานได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeleteOrganization() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteOrganization(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบหน่วยงานได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

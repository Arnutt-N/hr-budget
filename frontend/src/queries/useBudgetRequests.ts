import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import {
  fetchRequests,
  fetchRequestById,
  createRequest,
  updateRequest,
  deleteRequest,
  submitRequest,
  approveRequest,
  rejectRequest,
} from '@/api/budgetRequests'
import type {
  CreateBudgetRequest,
  ListFilters,
  ListMeta,
  UpdateBudgetRequest,
} from '@/types/budget-request'

const KEY = ['budget-requests'] as const
// Submit/approve/reject dispatch notifications server-side → refresh the bell too.
const NOTIF_KEY = ['notifications'] as const

/** Paginated list. Pass a `ref<ListFilters>` so editing filters refetches. */
export function useBudgetRequestList(filters: MaybeRef<ListFilters> = {}) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'list', toValue(filters)]),
    queryFn: async () => {
      const res = await fetchRequests(toValue(filters))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดคำขอไม่สำเร็จ')
      return { data: res.data, meta: (res.meta as ListMeta | undefined) ?? null }
    },
  })
}

export function useBudgetRequest(id: MaybeRef<number>) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'detail', toValue(id)]),
    queryFn: async () => {
      const res = await fetchRequestById(toValue(id))
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่พบคำขอ')
      return res.data
    },
    enabled: () => toValue(id) > 0,
  })
}

export function useCreateBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateBudgetRequest) => {
      const res = await createRequest(data)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถสร้างคำขอได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useUpdateBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { id: number; data: UpdateBudgetRequest }) => {
      const res = await updateRequest(vars.id, vars.data)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถแก้ไขคำขอได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useDeleteBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteRequest(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบคำขอได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useSubmitBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await submitRequest(id)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถส่งอนุมัติได้')
      return res.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: KEY })
      qc.invalidateQueries({ queryKey: NOTIF_KEY })
    },
  })
}

export function useApproveBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { id: number; note?: string }) => {
      const res = await approveRequest(vars.id, vars.note)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถอนุมัติได้')
      return res.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: KEY })
      qc.invalidateQueries({ queryKey: NOTIF_KEY })
    },
  })
}

export function useRejectBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { id: number; note: string }) => {
      const res = await rejectRequest(vars.id, vars.note)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถปฏิเสธได้')
      return res.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: KEY })
      qc.invalidateQueries({ queryKey: NOTIF_KEY })
    },
  })
}

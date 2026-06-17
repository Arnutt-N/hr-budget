import { computed, unref, type MaybeRef } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  fetchApprovalLevels,
  fetchApprovalStatus,
  approveStep,
  rejectStep,
} from '@/api/approvalChain'

const statusKey = (requestId: number) => ['requests', requestId, 'approval'] as const

/** The configured chain levels (shared across requests). */
export function useApprovalLevels() {
  return useQuery({
    queryKey: ['approval-levels'] as const,
    queryFn: async () => {
      const res = await fetchApprovalLevels()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดสายอนุมัติไม่สำเร็จ')
      return res.data
    },
    staleTime: 10 * 60 * 1000,
  })
}

export function useApprovalStatus(requestId: MaybeRef<number>) {
  const id = computed(() => unref(requestId))
  return useQuery({
    queryKey: computed(() => statusKey(id.value)),
    queryFn: async () => {
      const res = await fetchApprovalStatus(id.value)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดสถานะอนุมัติไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => id.value > 0),
  })
}

/**
 * Invalidate the chain status AND the budget-request queries so the page's
 * status badge + history reflect the new state immediately. The
 * ['budget-requests'] prefix covers both the list and detail keys
 * (['budget-requests','list',…] / ['budget-requests','detail',id]); the bell
 * refreshes because acting dispatches notifications server-side.
 */
function invalidateRequest(qc: ReturnType<typeof useQueryClient>, id: number): void {
  qc.invalidateQueries({ queryKey: statusKey(id) })
  qc.invalidateQueries({ queryKey: ['budget-requests'] })
  qc.invalidateQueries({ queryKey: ['notifications'] })
}

export function useApproveStep(requestId: MaybeRef<number>) {
  const qc = useQueryClient()
  const id = computed(() => unref(requestId))
  return useMutation({
    mutationFn: async (note?: string) => {
      const res = await approveStep(id.value, note)
      if (!res.success) throw new Error(res.error ?? 'อนุมัติไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => invalidateRequest(qc, id.value),
  })
}

export function useRejectStep(requestId: MaybeRef<number>) {
  const qc = useQueryClient()
  const id = computed(() => unref(requestId))
  return useMutation({
    mutationFn: async (note: string) => {
      const res = await rejectStep(id.value, note)
      if (!res.success) throw new Error(res.error ?? 'ปฏิเสธไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => invalidateRequest(qc, id.value),
  })
}

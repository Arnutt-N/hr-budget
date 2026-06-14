import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import {
  fetchSessions,
  fetchSession,
  createSession,
  deleteSession,
  fetchActivities,
  createRecord,
  fetchRecord,
  saveRecord,
  fetchExpenseStructure,
} from '@/api/disbursements'
import type {
  CreateRecordBody,
  CreateSessionBody,
  SaveRecordBody,
  SessionFilters,
  SessionListMeta,
} from '@/types/disbursement'

const KEY = ['disbursements'] as const

/** Paginated session list. Pass a `ref<SessionFilters>` so editing filters refetches. */
export function useDisbursementSessions(filters: MaybeRef<SessionFilters> = {}) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'sessions', toValue(filters)]),
    queryFn: async () => {
      const res = await fetchSessions(toValue(filters))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return { data: res.data, meta: (res.meta as SessionListMeta | undefined) ?? null }
    },
  })
}

export function useDisbursementSession(id: MaybeRef<number>) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'session', toValue(id)]),
    queryFn: async () => {
      const res = await fetchSession(toValue(id))
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่พบรอบการบันทึก')
      return res.data
    },
    enabled: () => toValue(id) > 0,
  })
}

export function useSessionActivities(sessionId: MaybeRef<number>) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'activities', toValue(sessionId)]),
    queryFn: async () => {
      const res = await fetchActivities(toValue(sessionId))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดกิจกรรมไม่สำเร็จ')
      return res.data
    },
    enabled: () => toValue(sessionId) > 0,
  })
}

export function useDisbursementRecord(id: MaybeRef<number>) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'record', toValue(id)]),
    queryFn: async () => {
      const res = await fetchRecord(toValue(id))
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่พบข้อมูลการบันทึก')
      return res.data
    },
    enabled: () => toValue(id) > 0,
  })
}

/** Reference data — never changes mid-session; avoid refetch churn across wizard steps. */
export function useExpenseStructure() {
  return useQuery({
    queryKey: [...KEY, 'expense-structure'],
    queryFn: async () => {
      const res = await fetchExpenseStructure()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดโครงสร้างรายจ่ายไม่สำเร็จ')
      return res.data
    },
    staleTime: Infinity,
    gcTime: Infinity,
  })
}

export function useCreateSession() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (body: CreateSessionBody) => {
      const res = await createSession(body)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถสร้างรอบการบันทึกได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useDeleteSession() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteSession(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบรอบการบันทึกได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: KEY }),
  })
}

export function useCreateRecord() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (body: CreateRecordBody) => {
      const res = await createRecord(body)
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่สามารถสร้างรายการได้')
      return res.data
    },
    onSuccess: (_data, body) => {
      qc.invalidateQueries({ queryKey: KEY })
      qc.invalidateQueries({ queryKey: [...KEY, 'activities', body.session_id] })
    },
  })
}

export function useSaveRecord() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { id: number; body: SaveRecordBody }) => {
      const res = await saveRecord(vars.id, vars.body)
      if (!res.success || !res.data) throw new Error(res.error ?? 'บันทึกไม่สำเร็จ')
      return res.data
    },
    onSuccess: (_data, vars) => {
      qc.invalidateQueries({ queryKey: KEY })
      qc.invalidateQueries({ queryKey: [...KEY, 'record', vars.id] })
    },
  })
}

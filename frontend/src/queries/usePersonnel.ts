import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { Ref } from 'vue'
import { computed } from 'vue'
import type {
  CreateVacancyRecruitment,
  CreatePersonnelBudgetPolicy,
  CreatePersonnelAllowance,
  CreatePersonnelAssignment,
  ComputeBudgetResult,
} from '@/types/personnel'
import {
  fetchPositionAllowances,
  createPositionAllowance,
  deletePositionAllowance,
  fetchVacancyRecruitment,
  createVacancyRecruitment,
  deleteVacancyRecruitment,
  fetchPersonnelBudgetPolicies,
  createPersonnelBudgetPolicy,
  updatePersonnelBudgetPolicy,
  fetchPersonnelAllowances,
  createPersonnelAllowance,
  deletePersonnelAllowance,
  fetchPersonnelAssignments,
  createPersonnelAssignment,
  deletePersonnelAssignment,
  computePersonnelBudget,
} from '@/api/personnel'

// ---------- position allowances ----------
export function usePositionAllowances(positionId: Ref<number | null>) {
  return useQuery({
    queryKey: computed(() => ['position-allowances', positionId.value]),
    enabled: computed(() => positionId.value !== null),
    queryFn: async () => {
      const res = await fetchPositionAllowances(positionId.value!)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePositionAllowance() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({
      positionId,
      data,
    }: {
      positionId: number
      data: { allowance_type_id: number; effective_from: string; doc_no?: string | null }
    }) => {
      const res = await createPositionAllowance(positionId, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถเพิ่มสิทธิ์ได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['position-allowances'] }),
  })
}

export function useDeletePositionAllowance() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ positionId, allowanceId }: { positionId: number; allowanceId: number }) => {
      const res = await deletePositionAllowance(positionId, allowanceId)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบสิทธิ์ได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['position-allowances'] }),
  })
}

// ---------- vacancy recruitment ----------
export function useVacancyRecruitmentList() {
  return useQuery({
    queryKey: ['vacancy-recruitment'],
    queryFn: async () => {
      const res = await fetchVacancyRecruitment()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreateVacancyRecruitment() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreateVacancyRecruitment) => {
      const res = await createVacancyRecruitment(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถบันทึกหลักฐานได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['vacancy-recruitment'] }),
  })
}

export function useDeleteVacancyRecruitment() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteVacancyRecruitment(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบหลักฐานได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['vacancy-recruitment'] }),
  })
}

// ---------- personnel budget policies ----------
export function usePersonnelBudgetPolicies() {
  return useQuery({
    queryKey: ['personnel-budget-policies'],
    queryFn: async () => {
      const res = await fetchPersonnelBudgetPolicies()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePersonnelBudgetPolicy() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreatePersonnelBudgetPolicy) => {
      const res = await createPersonnelBudgetPolicy(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างนโยบายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-budget-policies'] }),
  })
}

export function useUpdatePersonnelBudgetPolicy() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({
      id,
      data,
    }: {
      id: number
      data: { vacancy_rule?: string | null; calc_mode?: string; buffer_percent?: number | null; reference_date?: string | null }
    }) => {
      const res = await updatePersonnelBudgetPolicy(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขนโยบายได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-budget-policies'] }),
  })
}

// ---------- personnel allowances (actuals) ----------
export function usePersonnelAllowanceList() {
  return useQuery({
    queryKey: ['personnel-allowances'],
    queryFn: async () => {
      const res = await fetchPersonnelAllowances()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePersonnelAllowance() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreatePersonnelAllowance) => {
      const res = await createPersonnelAllowance(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถบันทึกการรับจริงได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-allowances'] }),
  })
}

export function useDeletePersonnelAllowance() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deletePersonnelAllowance(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบการรับจริงได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-allowances'] }),
  })
}

// ---------- personnel assignments ----------
export function usePersonnelAssignmentList() {
  return useQuery({
    queryKey: ['personnel-assignments'],
    queryFn: async () => {
      const res = await fetchPersonnelAssignments()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePersonnelAssignment() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreatePersonnelAssignment) => {
      const res = await createPersonnelAssignment(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถบันทึกการไปช่วยราชการได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-assignments'] }),
  })
}

export function useDeletePersonnelAssignment() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deletePersonnelAssignment(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบการไปช่วยราชการได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['personnel-assignments'] }),
  })
}

// ---------- compute ----------
export function useComputePersonnelBudget() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ fiscalYearId, dryRun }: { fiscalYearId: number; dryRun: boolean }): Promise<ComputeBudgetResult> => {
      const res = await computePersonnelBudget(fiscalYearId, dryRun)
      if (!res.success || !res.data) throw new Error(res.error ?? 'คำนวณไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['budget-line-items'] })
    },
  })
}

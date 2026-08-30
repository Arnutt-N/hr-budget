import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import type { CreatePosition, UpdatePosition, CreatePositionVersion } from '@/types/position'
import {
  fetchPositions,
  createPosition,
  updatePosition,
  deletePosition,
  fetchPositionVersions,
  createPositionVersion,
  type PositionFilters,
} from '@/api/positions'
import type { Ref } from 'vue'
import { computed } from 'vue'

const QUERY_KEY = ['positions'] as const

export function usePositionList(filters: Ref<PositionFilters>) {
  return useQuery({
    queryKey: computed(() => [...QUERY_KEY, filters.value]),
    queryFn: async () => {
      const first = await fetchPositions(filters.value)
      if (!first.success || !first.data) throw new Error(first.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      // Fetch every page — the server caps per_page at 100, so a single fetch
      // would silently hide positions past the 100th from the table and selects.
      const totalPages = Number(first.meta?.total_pages ?? 1)
      if (!(totalPages > 1)) return first.data
      const rest = await Promise.all(
        Array.from({ length: totalPages - 1 }, (_, i) => fetchPositions(filters.value, i + 2)),
      )
      if (rest.some((r) => !r.success || !r.data)) throw new Error('โหลดข้อมูลไม่สำเร็จ')
      return [...first.data, ...rest.flatMap((r) => r.data ?? [])]
    },
  })
}

export function useCreatePosition() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (data: CreatePosition) => {
      const res = await createPosition(data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถสร้างอัตรากำลังได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useUpdatePosition() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: UpdatePosition }) => {
      const res = await updatePosition(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถแก้ไขอัตรากำลังได้')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function useDeletePosition() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deletePosition(id)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถลบอัตรากำลังได้')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: QUERY_KEY }),
  })
}

export function usePositionVersions(id: Ref<number | null>) {
  return useQuery({
    queryKey: computed(() => ['position-versions', id.value]),
    enabled: computed(() => id.value !== null),
    queryFn: async () => {
      const res = await fetchPositionVersions(id.value!)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}

export function useCreatePositionVersion() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: CreatePositionVersion }) => {
      const res = await createPositionVersion(id, data)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถเพิ่มเวอร์ชันได้')
      return res.data
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: QUERY_KEY })
      qc.invalidateQueries({ queryKey: ['position-versions'] })
    },
  })
}

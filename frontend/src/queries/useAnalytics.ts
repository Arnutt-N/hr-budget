import { useQuery } from '@tanstack/vue-query'
import { computed, type Ref } from 'vue'
import { fetchComparison, fetchForecast, fetchRequestVsApproved } from '@/api/analytics'
import type { ComparisonDimension } from '@/types/analytics'

const QUERY_KEY = ['analytics'] as const

/**
 * Comparison report. `active` lets the page gate the query to the visible tab
 * (lazy per-tab fetch) — folded into `enabled` alongside the year guard.
 */
export function useComparison(
  year: Ref<number | undefined>,
  dimension: Ref<ComparisonDimension>,
  active: Ref<boolean>,
) {
  return useQuery({
    queryKey: computed(() => [...QUERY_KEY, 'comparison', year.value, dimension.value]),
    queryFn: async () => {
      const res = await fetchComparison(year.value as number, dimension.value)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลเปรียบเทียบไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => active.value && !!year.value),
  })
}

export function useForecast(year: Ref<number | undefined>, active: Ref<boolean>) {
  return useQuery({
    queryKey: computed(() => [...QUERY_KEY, 'forecast', year.value]),
    queryFn: async () => {
      const res = await fetchForecast(year.value as number)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูล Forecast ไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => active.value && !!year.value),
  })
}

export function useRequestVsApproved(year: Ref<number | undefined>, active: Ref<boolean>) {
  return useQuery({
    queryKey: computed(() => [...QUERY_KEY, 'request-vs-approved', year.value]),
    queryFn: async () => {
      const res = await fetchRequestVsApproved(year.value as number)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลคำขอ vs อนุมัติไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => active.value && !!year.value),
  })
}

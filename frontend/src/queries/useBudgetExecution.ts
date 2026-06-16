import { useQuery } from '@tanstack/vue-query'
import { computed, type Ref } from 'vue'
import { fetchExecutionReport, fetchExecutionYears } from '@/api/budgetExecution'

const QUERY_KEY = ['budget-execution'] as const

export function useExecutionReport(year: Ref<number | undefined>, org: Ref<number | null>) {
  return useQuery({
    queryKey: computed(() => [...QUERY_KEY, 'report', year.value, org.value]),
    queryFn: async () => {
      const res = await fetchExecutionReport(year.value, org.value)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดรายงานไม่สำเร็จ')
      return res.data
    },
    enabled: computed(() => !!year.value),
  })
}

export function useExecutionYears() {
  return useQuery({
    queryKey: [...QUERY_KEY, 'years'],
    queryFn: async () => {
      const res = await fetchExecutionYears()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดปีงบประมาณไม่สำเร็จ')
      return res.data
    },
  })
}

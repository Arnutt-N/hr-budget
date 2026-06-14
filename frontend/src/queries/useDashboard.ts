import { useQuery } from '@tanstack/vue-query'
import { fetchDashboardSummary, fetchMonthlyChart } from '@/api/dashboard'

const QUERY_KEY = ['dashboard'] as const

export function useDashboardSummary() {
  return useQuery({
    queryKey: [...QUERY_KEY, 'summary'],
    queryFn: async () => {
      const res = await fetchDashboardSummary()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลแดชบอร์ดไม่สำเร็จ')
      return res.data
    },
  })
}

export function useMonthlyChart() {
  return useQuery({
    queryKey: [...QUERY_KEY, 'chart'],
    queryFn: async () => {
      const res = await fetchMonthlyChart()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลกราฟไม่สำเร็จ')
      return res.data
    },
  })
}

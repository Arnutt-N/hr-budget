import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import { fetchRequestFiles, uploadRequestFile, deleteFile } from '@/api/files'

const fileKey = (requestId: number) => ['request-files', requestId] as const

/** Attachments for one budget request. Skips fetching until id > 0. */
export function useRequestFiles(requestId: MaybeRef<number>) {
  return useQuery({
    queryKey: computed(() => ['request-files', toValue(requestId)]),
    queryFn: async () => {
      const res = await fetchRequestFiles(toValue(requestId))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดไฟล์ไม่สำเร็จ')
      return res.data
    },
    enabled: () => toValue(requestId) > 0,
  })
}

export function useUploadRequestFile() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { requestId: number; file: File }) => {
      const res = await uploadRequestFile(vars.requestId, vars.file)
      if (!res.success || !res.data) throw new Error(res.error ?? 'อัปโหลดไม่สำเร็จ')
      return res.data
    },
    onSuccess: (_data, vars) => qc.invalidateQueries({ queryKey: fileKey(vars.requestId) }),
  })
}

export function useDeleteRequestFile() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { requestId: number; fileId: number }) => {
      const res = await deleteFile(vars.fileId)
      if (!res.success) throw new Error(res.error ?? 'ลบไฟล์ไม่สำเร็จ')
    },
    onSuccess: (_data, vars) => qc.invalidateQueries({ queryKey: fileKey(vars.requestId) }),
  })
}

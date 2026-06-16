import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { computed, toValue, type MaybeRef } from 'vue'
import {
  fetchYears,
  fetchFolders,
  fetchFiles,
  createFolder,
  deleteFolder,
  uploadVaultFile,
  deleteVaultFile,
  initializeVaultYear,
  type CreateFolderPayload,
} from '@/api/vault'

const VAULT_KEY = ['vault'] as const

export function useVaultYears() {
  return useQuery({
    queryKey: ['vault', 'years'],
    queryFn: async () => {
      const res = await fetchYears()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดปีงบประมาณไม่สำเร็จ')
      return res.data
    },
  })
}

/** Folders (roots when parentId is null, children otherwise) + breadcrumb. */
export function useVaultFolders(year: MaybeRef<number>, parentId: MaybeRef<number | null>) {
  return useQuery({
    queryKey: computed(() => ['vault', 'folders', toValue(year), toValue(parentId)]),
    queryFn: async () => {
      const res = await fetchFolders(toValue(year), toValue(parentId))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดโฟลเดอร์ไม่สำเร็จ')
      return res.data
    },
    enabled: () => toValue(year) > 0,
  })
}

/** Files in the current folder. Disabled at the root (parentId null). */
export function useVaultFiles(folderId: MaybeRef<number | null>) {
  return useQuery({
    queryKey: computed(() => ['vault', 'files', toValue(folderId)]),
    queryFn: async () => {
      const id = toValue(folderId)
      if (id === null) return []
      const res = await fetchFiles(id)
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดไฟล์ไม่สำเร็จ')
      return res.data
    },
    enabled: () => toValue(folderId) !== null,
  })
}

export function useCreateFolder() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (payload: CreateFolderPayload) => {
      const res = await createFolder(payload)
      if (!res.success) throw new Error(res.error ?? 'สร้างโฟลเดอร์ไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: VAULT_KEY }),
  })
}

export function useInitializeVaultYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (year: number) => {
      const res = await initializeVaultYear(year)
      if (!res.success || !res.data) throw new Error(res.error ?? 'สร้างโครงสร้างโฟลเดอร์ไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: VAULT_KEY }),
  })
}

export function useDeleteFolder() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteFolder(id)
      if (!res.success) throw new Error(res.error ?? 'ลบโฟลเดอร์ไม่สำเร็จ')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: VAULT_KEY }),
  })
}

export function useUploadVaultFile() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (vars: { folderId: number; file: File }) => {
      const res = await uploadVaultFile(vars.folderId, vars.file)
      if (!res.success || !res.data) throw new Error(res.error ?? 'อัปโหลดไม่สำเร็จ')
      return res.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: VAULT_KEY }),
  })
}

export function useDeleteVaultFile() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const res = await deleteVaultFile(id)
      if (!res.success) throw new Error(res.error ?? 'ลบไฟล์ไม่สำเร็จ')
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: VAULT_KEY }),
  })
}

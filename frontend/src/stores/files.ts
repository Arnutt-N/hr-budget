import { ref } from 'vue'
import { defineStore } from 'pinia'
import { fetchRequestFiles, uploadRequestFile, deleteFile } from '@/api/files'
import type { FileAttachment } from '@/types/file'

export const useFileStore = defineStore('files', () => {
  const filesByRequest = ref<Record<number, FileAttachment[]>>({})
  const loading = ref(false)
  const error = ref<string | null>(null)

  function filesForRequest(requestId: number): FileAttachment[] {
    return filesByRequest.value[requestId] ?? []
  }

  async function fetchForRequest(requestId: number): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const result = await fetchRequestFiles(requestId)
      if (result.success && result.data) {
        filesByRequest.value = { ...filesByRequest.value, [requestId]: result.data }
        return true
      }
      error.value = result.error ?? 'เกิดข้อผิดพลาด'
      return false
    } catch {
      error.value = 'ไม่สามารถโหลดไฟล์ได้'
      return false
    } finally {
      loading.value = false
    }
  }

  async function upload(requestId: number, file: File): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      const result = await uploadRequestFile(requestId, file)
      if (result.success && result.data) {
        const current = filesByRequest.value[requestId] ?? []
        filesByRequest.value = { ...filesByRequest.value, [requestId]: [...current, result.data] }
        return true
      }
      error.value = result.error ?? 'อัปโหลดไม่สำเร็จ'
      return false
    } catch {
      error.value = 'อัปโหลดไม่สำเร็จ'
      return false
    } finally {
      loading.value = false
    }
  }

  async function remove(requestId: number, fileId: number): Promise<boolean> {
    try {
      const result = await deleteFile(fileId)
      if (result.success) {
        const current = filesByRequest.value[requestId] ?? []
        filesByRequest.value = {
          ...filesByRequest.value,
          [requestId]: current.filter((f) => f.id !== fileId),
        }
        return true
      }
      error.value = result.error ?? 'ลบไฟล์ไม่สำเร็จ'
      return false
    } catch {
      error.value = 'ลบไฟล์ไม่สำเร็จ'
      return false
    }
  }

  return { filesByRequest, loading, error, filesForRequest, fetchForRequest, upload, remove }
})

import { useConfirm } from 'primevue/useconfirm'

export function useDeleteConfirm() {
  const confirm = useConfirm()
  return function confirmDelete(options: {
    message: string
    accept: () => void | Promise<void>
  }): void {
    confirm.require({
      header: 'ยืนยันการลบ',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'ลบ',
      rejectLabel: 'ยกเลิก',
      acceptClass: 'p-button-danger',
      ...options,
    })
  }
}

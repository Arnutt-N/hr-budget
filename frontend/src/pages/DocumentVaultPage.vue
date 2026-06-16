<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Message from 'primevue/message'
import Tag from 'primevue/tag'
import { useAuthStore } from '@/stores/auth'
import { vaultFileDownloadUrl } from '@/api/vault'
import {
  useVaultYears,
  useVaultFolders,
  useVaultFiles,
  useCreateFolder,
  useDeleteFolder,
  useUploadVaultFile,
  useDeleteVaultFile,
  useInitializeVaultYear,
} from '@/queries/useVault'
import type { VaultFolder, VaultFile, Breadcrumb } from '@/types/vault'

const auth = useAuthStore()
const confirm = useConfirm()
const toast = useToast()

const canMutate = computed(() => ['admin', 'editor'].includes(auth.user?.role ?? ''))

// Default to the current BE fiscal year (Oct 1 boundary), same rule as the layout.
const currentFiscalYear = (() => {
  const now = new Date()
  const beYear = now.getFullYear() + 543
  return now.getMonth() + 1 >= 10 ? beYear + 1 : beYear
})()

const year = ref<number>(currentFiscalYear)
const currentFolderId = ref<number | null>(null)

const { data: years } = useVaultYears()
const yearOptions = computed(() => {
  const list = (years.value ?? []).map((y) => y.fiscal_year)
  if (!list.includes(currentFiscalYear)) list.unshift(currentFiscalYear)
  return list.sort((a, b) => b - a).map((y) => ({ label: `ปีงบ ${y}`, value: y }))
})

const {
  data: folderData,
  isLoading: foldersLoading,
  isError: foldersError,
  error: foldersErr,
} = useVaultFolders(year, currentFolderId)

const { data: files, isLoading: filesLoading } = useVaultFiles(currentFolderId)

const folders = computed<VaultFolder[]>(() => folderData.value?.folders ?? [])
const breadcrumb = computed<Breadcrumb[]>(() => folderData.value?.breadcrumb ?? [])

const createMutation = useCreateFolder()
const deleteFolderMutation = useDeleteFolder()
const uploadMutation = useUploadVaultFile()
const deleteFileMutation = useDeleteVaultFile()
const initMutation = useInitializeVaultYear()
const initializing = computed(() => initMutation.isPending.value)

// Scaffold the selected year's standard (system) folders from budget categories.
// Replaces the legacy `/files/init` web action.
async function onInitializeYear(): Promise<void> {
  try {
    const res = await initMutation.mutateAsync(year.value)
    toast.add({
      severity: 'success',
      summary: 'สร้างโครงสร้างโฟลเดอร์สำเร็จ',
      detail: `เพิ่ม ${res.created} โฟลเดอร์มาตรฐานสำหรับปีงบ ${year.value}`,
      life: 4000,
    })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'สร้างไม่สำเร็จ', detail: message, life: 5000 })
  }
}

// ── Navigation ──────────────────────────────────────────────────────────
function openFolder(id: number): void {
  currentFolderId.value = id
}
function goToRoot(): void {
  currentFolderId.value = null
}
function goToCrumb(id: number): void {
  currentFolderId.value = id
}
function onYearChange(): void {
  currentFolderId.value = null
}

// ── Create folder dialog ────────────────────────────────────────────────
const showDialog = ref(false)
const saving = computed(() => createMutation.isPending.value)
const schema = toTypedSchema(
  z.object({
    name: z
      .string({ required_error: 'กรุณาระบุชื่อโฟลเดอร์' })
      .min(1, 'กรุณาระบุชื่อโฟลเดอร์')
      .max(255, 'ชื่อต้องไม่เกิน 255 ตัวอักษร'),
    description: z.string().optional(),
  }),
)
const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [name] = defineField('name')
const [description] = defineField('description')

function openCreate(): void {
  resetForm({ values: { name: '', description: '' } })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    await createMutation.mutateAsync({
      name: values.name,
      description: values.description || null,
      parent_id: currentFolderId.value,
      fiscal_year: currentFolderId.value === null ? year.value : null,
    })
    toast.add({ severity: 'success', summary: 'สร้างโฟลเดอร์สำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'สร้างไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDeleteFolder(folder: VaultFolder): void {
  confirm.require({
    message: `ยืนยันลบโฟลเดอร์ "${folder.name}" และไฟล์ทั้งหมดภายใน?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteFolderMutation.mutateAsync(folder.id)
        toast.add({ severity: 'success', summary: 'ลบโฟลเดอร์สำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

// ── Upload ──────────────────────────────────────────────────────────────
const fileInput = ref<HTMLInputElement | null>(null)
const ACCEPT = '.pdf,.xlsx,.xls,.csv,.doc,.docx,.png,.jpg,.jpeg,.gif'
const uploading = computed(() => uploadMutation.isPending.value)

function triggerUpload(): void {
  fileInput.value?.click()
}

async function onFileSelected(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file || currentFolderId.value === null) return
  try {
    await uploadMutation.mutateAsync({ folderId: currentFolderId.value, file })
    toast.add({ severity: 'success', summary: 'อัปโหลดไฟล์สำเร็จ', life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'อัปโหลดไม่สำเร็จ', detail: message, life: 5000 })
  } finally {
    input.value = ''
  }
}

function confirmDeleteFile(file: VaultFile): void {
  confirm.require({
    message: `ยืนยันลบไฟล์ "${file.original_name}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteFileMutation.mutateAsync(file.id)
        toast.add({ severity: 'success', summary: 'ลบไฟล์สำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

// ── Helpers ─────────────────────────────────────────────────────────────
function formatSize(bytes: number): string {
  if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(2)} MB`
  if (bytes >= 1024) return `${(bytes / 1024).toFixed(2)} KB`
  return `${bytes} bytes`
}
</script>

<template>
  <div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-bold text-white">คลังเอกสาร</h1>
      <div class="flex items-center gap-2">
        <Select
          v-model="year"
          :options="yearOptions"
          option-label="label"
          option-value="value"
          class="w-40"
          @change="onYearChange"
        />
        <Button
          v-if="canMutate"
          label="สร้างโฟลเดอร์"
          icon="pi pi-folder-plus"
          severity="secondary"
          @click="openCreate"
        />
        <Button
          v-if="canMutate"
          label="อัปโหลดไฟล์"
          icon="pi pi-upload"
          :disabled="currentFolderId === null"
          :loading="uploading"
          @click="triggerUpload"
        />
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          :accept="ACCEPT"
          @change="onFileSelected"
        />
      </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="mb-4 flex flex-wrap items-center gap-1 text-sm" aria-label="breadcrumb">
      <button type="button" class="text-primary-400 hover:underline" @click="goToRoot">
        คลังเอกสาร (ปีงบ {{ year }})
      </button>
      <template v-for="crumb in breadcrumb" :key="crumb.id">
        <span class="text-dark-muted">/</span>
        <button
          type="button"
          class="text-primary-400 hover:underline"
          @click="goToCrumb(crumb.id)"
        >
          {{ crumb.name }}
        </button>
      </template>
    </nav>

    <Message v-if="foldersError" severity="error" :closable="false">
      {{ foldersErr?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <template v-else>
      <!-- Folders grid -->
      <div class="mb-6">
        <h2 class="mb-2 text-sm font-semibold uppercase tracking-wider text-dark-muted">โฟลเดอร์</h2>
        <p v-if="foldersLoading" class="py-4 text-dark-muted">กำลังโหลด…</p>
        <div v-else-if="folders.length === 0" class="py-6 text-center">
          <p class="text-dark-muted">
            {{ currentFolderId === null ? `ปีงบ ${year} ยังไม่มีโฟลเดอร์` : 'ไม่มีโฟลเดอร์ในระดับนี้' }}
          </p>
          <Button
            v-if="canMutate && currentFolderId === null"
            label="สร้างโครงสร้างโฟลเดอร์มาตรฐาน"
            icon="pi pi-sitemap"
            severity="secondary"
            class="mt-3"
            :loading="initializing"
            @click="onInitializeYear"
          />
        </div>
        <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="folder in folders"
            :key="folder.id"
            class="flex items-center justify-between rounded-lg border border-dark-border bg-dark-card p-3 transition hover:border-primary-500"
          >
            <button
              type="button"
              class="flex flex-1 items-center gap-3 text-left"
              @click="openFolder(folder.id)"
            >
              <i class="pi pi-folder text-2xl text-amber-400"></i>
              <span class="min-w-0">
                <span class="block truncate font-medium text-white">{{ folder.name }}</span>
                <span class="block text-xs text-dark-muted">
                  {{ folder.subfolder_count ?? 0 }} โฟลเดอร์ · {{ folder.file_count ?? 0 }} ไฟล์
                </span>
              </span>
            </button>
            <Tag v-if="folder.is_system" value="ระบบ" severity="info" class="ml-2" />
            <Button
              v-else-if="canMutate"
              icon="pi pi-trash"
              size="small"
              text
              severity="danger"
              aria-label="ลบโฟลเดอร์"
              @click="confirmDeleteFolder(folder)"
            />
          </div>
        </div>
      </div>

      <!-- Files in current folder -->
      <div v-if="currentFolderId !== null">
        <h2 class="mb-2 text-sm font-semibold uppercase tracking-wider text-dark-muted">ไฟล์</h2>
        <DataTable
          :value="files ?? []"
          :loading="filesLoading"
          paginator
          :rows="10"
          data-key="id"
          class="overflow-hidden rounded-lg border border-dark-border shadow"
        >
          <template #empty>
            <p class="py-4 text-center text-dark-muted">ยังไม่มีไฟล์ในโฟลเดอร์นี้</p>
          </template>

          <Column field="original_name" header="ชื่อไฟล์" sortable>
            <template #body="{ data }">
              <span class="font-medium">{{ data.original_name }}</span>
            </template>
          </Column>
          <Column field="file_type" header="ประเภท" sortable>
            <template #body="{ data }">
              <Tag :value="(data.file_type as string).toUpperCase()" severity="secondary" />
            </template>
          </Column>
          <Column field="file_size" header="ขนาด" sortable>
            <template #body="{ data }">{{ formatSize(data.file_size) }}</template>
          </Column>
          <Column field="uploaded_by_name" header="ผู้อัปโหลด">
            <template #body="{ data }">{{ data.uploaded_by_name ?? '-' }}</template>
          </Column>
          <Column header="จัดการ" class="text-right">
            <template #body="{ data }">
              <div class="flex justify-end gap-1">
                <a
                  :href="vaultFileDownloadUrl(data.id)"
                  class="p-button p-button-text p-button-sm p-button-secondary"
                >
                  <i class="pi pi-download"></i>
                </a>
                <Button
                  v-if="canMutate"
                  icon="pi pi-trash"
                  size="small"
                  text
                  severity="danger"
                  aria-label="ลบไฟล์"
                  @click="confirmDeleteFile(data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </template>

    <!-- Create folder dialog -->
    <Dialog v-model:visible="showDialog" header="สร้างโฟลเดอร์ใหม่" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label for="folder-name" class="text-sm font-medium text-dark-muted">ชื่อโฟลเดอร์</label>
          <InputText id="folder-name" v-model.trim="name" maxlength="255" :invalid="!!errors.name" fluid />
          <small v-if="errors.name" class="text-red-600" role="alert">{{ errors.name }}</small>
        </div>
        <div class="flex flex-col gap-1">
          <label for="folder-desc" class="text-sm font-medium text-dark-muted">คำอธิบาย (ไม่บังคับ)</label>
          <InputText id="folder-desc" v-model.trim="description" fluid />
        </div>
        <p class="text-xs text-dark-muted">
          {{ currentFolderId === null ? `สร้างที่ระดับราก (ปีงบ ${year})` : 'สร้างเป็นโฟลเดอร์ย่อย' }}
        </p>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

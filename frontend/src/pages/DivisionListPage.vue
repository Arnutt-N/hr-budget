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
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { Division } from '@/types/division'
import {
  useDivisionList,
  useCreateDivision,
  useUpdateDivision,
  useDeleteDivision,
} from '@/queries/useDivisions'

const confirm = useConfirm()
const toast = useToast()

const { data: divisions, isLoading, isError, error } = useDivisionList()
const createMutation = useCreateDivision()
const updateMutation = useUpdateDivision()
const deleteMutation = useDeleteDivision()

// Same labels as migration 009 divisions.type enum
const TYPE_LABELS: Record<string, string> = {
  central: 'ส่วนกลาง',
  regional: 'ภูมิภาค',
  provincial: 'จังหวัด',
}
const typeOptions = Object.entries(TYPE_LABELS).map(([value, label]) => ({ value, label }))

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขกอง/สำนัก' : 'เพิ่มกอง/สำนัก'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    code: z.string({ required_error: 'กรุณาระบุรหัส' }).min(1, 'กรุณาระบุรหัส').max(20, 'รหัสต้องไม่เกิน 20 ตัวอักษร'),
    name_th: z.string({ required_error: 'กรุณาระบุชื่อกอง/สำนัก' }).min(1, 'กรุณาระบุชื่อกอง/สำนัก').max(255, 'ชื่อต้องไม่เกิน 255 ตัวอักษร'),
    short_name: z.string().optional(),
    type: z.string().optional(),
    is_active: z.boolean().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [code] = defineField('code')
const [nameTh] = defineField('name_th')
const [shortName] = defineField('short_name')
const [type] = defineField('type')
const [isActive] = defineField('is_active')

function openCreate(): void {
  editingId.value = null
  resetForm({ values: { code: '', name_th: '', short_name: '', type: 'central', is_active: true } })
  showDialog.value = true
}

function openEdit(division: Division): void {
  editingId.value = division.id
  resetForm({
    values: {
      code: division.code,
      name_th: division.name_th,
      short_name: division.short_name ?? '',
      type: division.type ?? 'central',
      is_active: !!division.is_active,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขกอง/สำนักสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มกอง/สำนักสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(division: Division): void {
  confirm.require({
    message: `ยืนยันลบกอง/สำนัก "${division.name_th}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(division.id)
        toast.add({ severity: 'success', summary: 'ลบกอง/สำนักสำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">กอง/สำนัก</h1>
      <Button label="เพิ่มกอง/สำนัก" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="divisions ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลกอง/สำนัก</p>
      </template>

      <Column field="code" header="รหัส" sortable>
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code }}</span>
        </template>
      </Column>
      <Column field="name_th" header="ชื่อกอง/สำนัก" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name_th }}</span>
          <span v-if="data.short_name" class="ml-1 text-sm text-dark-muted">({{ data.short_name }})</span>
        </template>
      </Column>
      <Column field="type" header="ประเภท" sortable>
        <template #body="{ data }">
          {{ data.type ? (TYPE_LABELS[data.type] ?? data.type) : '-' }}
        </template>
      </Column>
      <Column header="สถานะ">
        <template #body="{ data }">
          <Tag :value="data.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน'" :severity="data.is_active ? 'success' : 'secondary'" />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label for="div-code" class="text-sm font-medium text-dark-muted">รหัส</label>
          <InputText id="div-code" v-model.trim="code" maxlength="20" :invalid="!!errors.code" fluid />
          <small v-if="errors.code" class="text-red-600" role="alert">{{ errors.code }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="div-name" class="text-sm font-medium text-dark-muted">ชื่อกอง/สำนัก</label>
          <InputText id="div-name" v-model.trim="nameTh" :invalid="!!errors.name_th" fluid />
          <small v-if="errors.name_th" class="text-red-600" role="alert">{{ errors.name_th }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="div-short" class="text-sm font-medium text-dark-muted">ชื่อย่อ</label>
          <InputText id="div-short" v-model.trim="shortName" fluid />
        </div>

        <div class="flex flex-col gap-1">
          <label for="div-type" class="text-sm font-medium text-dark-muted">ประเภท</label>
          <Select
            v-model="type"
            label-id="div-type"
            :options="typeOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือก --"
            fluid
          />
        </div>

        <label v-if="editingId" class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isActive" binary input-id="div-is-active" />
          ใช้งาน
        </label>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import type { Division } from '@/types/division'
import {
  useDivisionList,
  useCreateDivision,
  useUpdateDivision,
  useDeleteDivision,
} from '@/queries/useDivisions'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

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

function onDelete(division: Division): void {
  confirmDelete({
    message: `ยืนยันลบกอง/สำนัก "${division.name_th}"?`,
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
    <PageHeader title="กอง/สำนัก">
      <Button label="เพิ่มกอง/สำนัก" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

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
        <ListEmptyState message="ยังไม่มีข้อมูลกอง/สำนัก" />
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
            <Button label="ลบ" size="small" text severity="danger" @click="onDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <FormField id="div-code" label="รหัส" :error="errors.code">
          <InputText
            id="div-code"
            v-model.trim="code"
            maxlength="20"
            :invalid="!!errors.code"
            :aria-describedby="errors.code ? 'div-code-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="div-name" label="ชื่อกอง/สำนัก" :error="errors.name_th">
          <InputText
            id="div-name"
            v-model.trim="nameTh"
            :invalid="!!errors.name_th"
            :aria-describedby="errors.name_th ? 'div-name-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="div-short" label="ชื่อย่อ">
          <InputText id="div-short" v-model.trim="shortName" fluid />
        </FormField>

        <FormField id="div-type" label="ประเภท">
          <Select
            v-model="type"
            label-id="div-type"
            :options="typeOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือก --"
            fluid
          />
        </FormField>

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

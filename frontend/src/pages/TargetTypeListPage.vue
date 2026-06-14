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
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { TargetType } from '@/types/target-type'
import {
  useTargetTypeList,
  useCreateTargetType,
  useUpdateTargetType,
  useDeleteTargetType,
} from '@/queries/useTargetTypes'

const confirm = useConfirm()
const toast = useToast()

const { data: targetTypes, isLoading, isError, error } = useTargetTypeList()
const createMutation = useCreateTargetType()
const updateMutation = useUpdateTargetType()
const deleteMutation = useDeleteTargetType()

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขประเภทเป้าหมาย' : 'เพิ่มประเภทเป้าหมาย'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    code: z.string({ required_error: 'กรุณาระบุรหัสประเภทเป้าหมาย' }).min(1, 'กรุณาระบุรหัสประเภทเป้าหมาย').max(50, 'รหัสต้องไม่เกิน 50 ตัวอักษร'),
    name_th: z.string({ required_error: 'กรุณาระบุชื่อประเภทเป้าหมาย' }).min(1, 'กรุณาระบุชื่อประเภทเป้าหมาย'),
    description: z.string().optional(),
    is_active: z.boolean().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [code] = defineField('code')
const [nameTh] = defineField('name_th')
const [description] = defineField('description')
const [isActive] = defineField('is_active')

function openCreate(): void {
  editingId.value = null
  resetForm({ values: { code: '', name_th: '', description: '', is_active: true } })
  showDialog.value = true
}

function openEdit(tt: TargetType): void {
  editingId.value = tt.id
  resetForm({
    values: {
      code: tt.code,
      name_th: tt.name_th,
      description: tt.description ?? '',
      is_active: !!tt.is_active,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขประเภทเป้าหมายสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มประเภทเป้าหมายสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(tt: TargetType): void {
  confirm.require({
    message: `ยืนยันลบประเภทเป้าหมาย "${tt.name_th}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(tt.id)
        toast.add({ severity: 'success', summary: 'ลบประเภทเป้าหมายสำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">ประเภทเป้าหมาย</h1>
      <Button label="เพิ่มประเภทเป้าหมาย" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="targetTypes ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลประเภทเป้าหมาย</p>
      </template>

      <Column field="code" header="รหัส" sortable>
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code }}</span>
        </template>
      </Column>
      <Column field="name_th" header="ชื่อประเภทเป้าหมาย" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name_th }}</span>
        </template>
      </Column>
      <Column field="description" header="คำอธิบาย">
        <template #body="{ data }">
          <span class="text-dark-muted">{{ data.description ?? '-' }}</span>
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
          <label for="tt-code" class="text-sm font-medium text-dark-muted">รหัสประเภทเป้าหมาย</label>
          <InputText id="tt-code" v-model.trim="code" maxlength="50" :invalid="!!errors.code" fluid />
          <small v-if="errors.code" class="text-red-600" role="alert">{{ errors.code }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="tt-name" class="text-sm font-medium text-dark-muted">ชื่อประเภทเป้าหมาย</label>
          <InputText id="tt-name" v-model.trim="nameTh" :invalid="!!errors.name_th" fluid />
          <small v-if="errors.name_th" class="text-red-600" role="alert">{{ errors.name_th }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="tt-desc" class="text-sm font-medium text-dark-muted">คำอธิบาย</label>
          <Textarea id="tt-desc" v-model.trim="description" rows="3" fluid />
        </div>

        <label v-if="editingId" class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isActive" binary input-id="tt-is-active" />
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

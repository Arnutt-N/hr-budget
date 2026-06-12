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
import InputNumber from 'primevue/inputnumber'
import Message from 'primevue/message'
import CategoryItemsPanel from '@/components/CategoryItemsPanel.vue'
import type { BudgetCategory } from '@/types/budget-category'
import {
  useCategoryList,
  useCreateCategory,
  useUpdateCategory,
  useDeleteCategory,
} from '@/queries/useCategories'

const confirm = useConfirm()
const toast = useToast()

const { data: categories, isLoading, isError, error } = useCategoryList()
const createMutation = useCreateCategory()
const updateMutation = useUpdateCategory()
const deleteMutation = useDeleteCategory()

// Expanded rows show the items panel (nested DataTable)
const expandedRows = ref<Record<number, boolean>>({})

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขหมวด' : 'เพิ่มหมวด'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    code: z.string({ required_error: 'กรุณาระบุรหัส' }).min(1, 'กรุณาระบุรหัส').max(20, 'รหัสต้องไม่เกิน 20 ตัวอักษร'),
    name_th: z.string({ required_error: 'กรุณาระบุชื่อหมวด' }).min(1, 'กรุณาระบุชื่อหมวด'),
    name_en: z.string().optional(),
    sort_order: z.coerce.number().int().min(0).optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [code] = defineField('code')
const [nameTh] = defineField('name_th')
const [nameEn] = defineField('name_en')
const [sortOrder] = defineField('sort_order')

function openCreate(): void {
  editingId.value = null
  resetForm({ values: { code: '', name_th: '', name_en: '', sort_order: 0 } })
  showDialog.value = true
}

function openEdit(cat: BudgetCategory): void {
  editingId.value = cat.id
  resetForm({
    values: {
      code: cat.code,
      name_th: cat.name_th,
      name_en: cat.name_en ?? '',
      sort_order: cat.sort_order ?? 0,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขหมวดสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มหมวดสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(cat: BudgetCategory): void {
  confirm.require({
    message: `ยืนยันลบหมวด "${cat.name_th}"? รายการทั้งหมดในหมวดจะถูกลบด้วย`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(cat.id)
        toast.add({ severity: 'success', summary: 'ลบหมวดสำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">ประเภทรายจ่าย</h1>
      <Button label="เพิ่มหมวด" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      v-model:expanded-rows="expandedRows"
      :value="categories ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลหมวดงบประมาณ</p>
      </template>

      <Column expander style="width: 3rem" />
      <Column field="code" header="รหัส" sortable>
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code }}</span>
        </template>
      </Column>
      <Column field="name_th" header="ชื่อหมวด" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name_th }}</span>
        </template>
      </Column>
      <Column field="level" header="ระดับ" sortable />
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>

      <template #expansion="{ data }">
        <CategoryItemsPanel :category-id="data.id" />
      </template>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label for="cat-code" class="text-sm font-medium text-dark-muted">รหัส</label>
          <InputText id="cat-code" v-model.trim="code" maxlength="20" :invalid="!!errors.code" fluid />
          <small v-if="errors.code" class="text-red-600" role="alert">{{ errors.code }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="cat-name-th" class="text-sm font-medium text-dark-muted">ชื่อหมวด (ไทย)</label>
          <InputText id="cat-name-th" v-model.trim="nameTh" :invalid="!!errors.name_th" fluid />
          <small v-if="errors.name_th" class="text-red-600" role="alert">{{ errors.name_th }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="cat-name-en" class="text-sm font-medium text-dark-muted">ชื่อหมวด (อังกฤษ)</label>
          <InputText id="cat-name-en" v-model.trim="nameEn" fluid />
        </div>

        <div class="flex flex-col gap-1">
          <label for="cat-sort" class="text-sm font-medium text-dark-muted">ลำดับ</label>
          <InputNumber v-model="sortOrder" input-id="cat-sort" :use-grouping="false" fluid />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

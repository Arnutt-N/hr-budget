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
import InputNumber from 'primevue/inputnumber'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import CategoryItemsPanel from '@/components/CategoryItemsPanel.vue'
import type { BudgetCategory } from '@/types/budget-category'
import {
  useCategoryList,
  useCreateCategory,
  useUpdateCategory,
  useDeleteCategory,
} from '@/queries/useCategories'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

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

function onDelete(cat: BudgetCategory): void {
  confirmDelete({
    message: `ยืนยันลบหมวด "${cat.name_th}"? รายการทั้งหมดในหมวดจะถูกลบด้วย`,
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
    <PageHeader title="ประเภทรายจ่าย">
      <Button label="เพิ่มหมวด" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <div class="table-scroll" v-else>
    <DataTable
      v-model:expanded-rows="expandedRows"
      :value="categories ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลหมวดงบประมาณ" />
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
            <Button label="ลบ" size="small" text severity="danger" @click="onDelete(data)" />
          </div>
        </template>
      </Column>

      <template #expansion="{ data }">
        <CategoryItemsPanel :category-id="data.id" />
      </template>
    </DataTable>
    </div>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <FormField id="cat-code" label="รหัส" :error="errors.code">
          <InputText
            id="cat-code"
            v-model.trim="code"
            maxlength="20"
            :invalid="!!errors.code"
            :aria-describedby="errors.code ? 'cat-code-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="cat-name-th" label="ชื่อหมวด (ไทย)" :error="errors.name_th">
          <InputText
            id="cat-name-th"
            v-model.trim="nameTh"
            :invalid="!!errors.name_th"
            :aria-describedby="errors.name_th ? 'cat-name-th-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="cat-name-en" label="ชื่อหมวด (อังกฤษ)">
          <InputText id="cat-name-en" v-model.trim="nameEn" fluid />
        </FormField>

        <FormField id="cat-sort" label="ลำดับ">
          <InputNumber v-model="sortOrder" input-id="cat-sort" :use-grouping="false" fluid />
        </FormField>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

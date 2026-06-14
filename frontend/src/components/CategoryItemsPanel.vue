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
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { BudgetCategoryItem } from '@/types/budget-category'
import {
  useCategoryItems,
  useCreateCategoryItem,
  useUpdateCategoryItem,
  useDeleteCategoryItem,
  useRestoreCategoryItem,
} from '@/queries/useCategories'

const props = defineProps<{ categoryId: number }>()

const confirm = useConfirm()
const toast = useToast()

const { data: items, isLoading, isError, error } = useCategoryItems(() => props.categoryId)
const createMutation = useCreateCategoryItem()
const updateMutation = useUpdateCategoryItem()
const deleteMutation = useDeleteCategoryItem()
const restoreMutation = useRestoreCategoryItem()

const showDialog = ref(false)
const editingItemId = ref<number | null>(null)
const dialogTitle = computed(() => (editingItemId.value ? 'แก้ไขรายการ' : 'เพิ่มรายการ'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    name: z.string({ required_error: 'กรุณาระบุชื่อรายการ' }).min(1, 'กรุณาระบุชื่อรายการ'),
    code: z.string().optional(),
    level: z.coerce.number().int().min(0).optional(),
    sort_order: z.coerce.number().int().min(0).optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [name] = defineField('name')
const [code] = defineField('code')
const [level] = defineField('level')
const [sortOrder] = defineField('sort_order')

function openCreate(): void {
  editingItemId.value = null
  resetForm({ values: { name: '', code: '', level: 1, sort_order: 0 } })
  showDialog.value = true
}

function openEdit(item: BudgetCategoryItem): void {
  editingItemId.value = item.id
  resetForm({
    values: {
      name: item.name,
      code: item.code ?? '',
      level: item.level,
      sort_order: item.sort_order,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingItemId.value) {
      await updateMutation.mutateAsync({
        categoryId: props.categoryId,
        itemId: editingItemId.value,
        data: values,
      })
      toast.add({ severity: 'success', summary: 'แก้ไขรายการสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync({ categoryId: props.categoryId, data: values })
      toast.add({ severity: 'success', summary: 'เพิ่มรายการสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(item: BudgetCategoryItem): void {
  confirm.require({
    message: `ยืนยันลบรายการ "${item.name}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync({ categoryId: props.categoryId, itemId: item.id })
        toast.add({ severity: 'success', summary: 'ลบรายการสำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

async function restore(item: BudgetCategoryItem): Promise<void> {
  try {
    await restoreMutation.mutateAsync({ categoryId: props.categoryId, itemId: item.id })
    toast.add({ severity: 'success', summary: 'กู้คืนรายการสำเร็จ', life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'กู้คืนไม่สำเร็จ', detail: message, life: 5000 })
  }
}
</script>

<template>
  <div class="px-4 py-3">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-dark-text">รายการในหมวด</h3>
      <Button label="เพิ่มรายการ" icon="pi pi-plus" size="small" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดรายการได้' }}
    </Message>

    <DataTable v-else :value="items ?? []" :loading="isLoading" data-key="id" size="small">
      <template #empty>
        <p class="py-3 text-center text-sm text-dark-muted">ยังไม่มีรายการในหมวดนี้</p>
      </template>

      <Column field="name" header="ชื่อรายการ">
        <template #body="{ data }">
          <span :style="{ paddingLeft: `${Math.max(0, (data.level ?? 1) - 1) * 16}px` }">
            <span :class="data.deleted_at ? 'text-dark-muted line-through' : ''">{{ data.name }}</span>
          </span>
        </template>
      </Column>
      <Column field="code" header="รหัส">
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code ?? '-' }}</span>
        </template>
      </Column>
      <Column field="level" header="ระดับ" />
      <Column field="sort_order" header="ลำดับ" />
      <Column header="สถานะ">
        <template #body="{ data }">
          <Tag v-if="data.deleted_at" value="ถูกลบ" severity="danger" />
          <Tag v-else :value="data.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน'" :severity="data.is_active ? 'success' : 'secondary'" />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button
              v-if="data.deleted_at"
              label="กู้คืน"
              size="small"
              text
              severity="success"
              :loading="restoreMutation.isPending.value"
              @click="restore(data)"
            />
            <template v-else>
              <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
              <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
            </template>
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label :for="`item-name-${categoryId}`" class="text-sm font-medium text-dark-muted">ชื่อรายการ</label>
          <InputText :id="`item-name-${categoryId}`" v-model.trim="name" :invalid="!!errors.name" fluid />
          <small v-if="errors.name" class="text-red-600" role="alert">{{ errors.name }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label :for="`item-code-${categoryId}`" class="text-sm font-medium text-dark-muted">รหัส</label>
          <InputText :id="`item-code-${categoryId}`" v-model.trim="code" fluid />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label :for="`item-level-${categoryId}`" class="text-sm font-medium text-dark-muted">ระดับ</label>
            <InputNumber v-model="level" :input-id="`item-level-${categoryId}`" :use-grouping="false" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <label :for="`item-sort-${categoryId}`" class="text-sm font-medium text-dark-muted">ลำดับ</label>
            <InputNumber v-model="sortOrder" :input-id="`item-sort-${categoryId}`" :use-grouping="false" fluid />
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

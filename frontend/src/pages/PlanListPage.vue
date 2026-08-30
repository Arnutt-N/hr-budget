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
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import type { Plan } from '@/types/plan'
import { usePlanList, useCreatePlan, useUpdatePlan, useDeletePlan } from '@/queries/usePlans'
import { useFiscalYearList, useFiscalYearOptions } from '@/queries/useFiscalYears'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

const { data: plans, isLoading, isError, error } = usePlanList()
const { data: fiscalYears } = useFiscalYearList()
const createMutation = useCreatePlan()
const updateMutation = useUpdatePlan()
const deleteMutation = useDeletePlan()

const yearOptions = useFiscalYearOptions()

function defaultFiscalYear(): number {
  const current = (fiscalYears.value ?? []).find((fy) => fy.is_current)
  return current ? current.year : new Date().getFullYear() + 543
}

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขแผนงาน' : 'เพิ่มแผนงาน'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    code: z.string().max(50, 'รหัสต้องไม่เกิน 50 ตัวอักษร').optional(),
    name_th: z
      .string({ required_error: 'กรุณาระบุชื่อแผนงาน/โครงการ' })
      .min(1, 'กรุณาระบุชื่อแผนงาน/โครงการ')
      .max(500, 'ชื่อต้องไม่เกิน 500 ตัวอักษร'),
    name_en: z.string().optional(),
    description: z.string().optional(),
    fiscal_year: z.coerce.number().int().min(2400, 'กรุณาเลือกปีงบประมาณ'),
    is_active: z.boolean().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [code] = defineField('code')
const [nameTh] = defineField('name_th')
const [nameEn] = defineField('name_en')
const [description] = defineField('description')
const [fiscalYear] = defineField('fiscal_year')
const [isActive] = defineField('is_active')

function openCreate(): void {
  editingId.value = null
  resetForm({
    values: {
      code: '',
      name_th: '',
      name_en: '',
      description: '',
      fiscal_year: defaultFiscalYear(),
      is_active: true,
    },
  })
  showDialog.value = true
}

function openEdit(plan: Plan): void {
  editingId.value = plan.id
  resetForm({
    values: {
      code: plan.code ?? '',
      name_th: plan.name_th,
      name_en: plan.name_en ?? '',
      description: plan.description ?? '',
      fiscal_year: plan.fiscal_year,
      is_active: !!plan.is_active,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขแผนงานสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มแผนงานสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function onDelete(plan: Plan): void {
  confirmDelete({
    message: `ยืนยันลบแผนงาน "${plan.name_th}"?`,
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(plan.id)
        toast.add({ severity: 'success', summary: 'ลบแผนงานสำเร็จ', life: 3000 })
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
    <PageHeader title="แผนงาน/ผลผลิต">
      <Button label="เพิ่มแผนงาน" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <DataTable
      v-else
      :value="plans ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลแผนงาน" />
      </template>

      <Column field="code" header="รหัส" sortable>
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code ?? '-' }}</span>
        </template>
      </Column>
      <Column field="name_th" header="ชื่อแผนงาน/ผลผลิต" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name_th }}</span>
        </template>
      </Column>
      <Column field="fiscal_year" header="ปีงบประมาณ" sortable>
        <template #body="{ data }">
          {{ data.fiscal_year }}
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
        <FormField id="plan-code" label="รหัส" :error="errors.code">
          <InputText
            id="plan-code"
            v-model.trim="code"
            maxlength="50"
            :invalid="!!errors.code"
            :aria-describedby="errors.code ? 'plan-code-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="plan-name" label="ชื่อแผนงาน/ผลผลิต" :error="errors.name_th">
          <InputText
            id="plan-name"
            v-model.trim="nameTh"
            :invalid="!!errors.name_th"
            :aria-describedby="errors.name_th ? 'plan-name-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="plan-name-en" label="ชื่อ (อังกฤษ)">
          <InputText id="plan-name-en" v-model.trim="nameEn" fluid />
        </FormField>

        <FormField id="plan-desc" label="คำอธิบาย">
          <Textarea id="plan-desc" v-model.trim="description" rows="3" fluid />
        </FormField>

        <FormField id="plan-year" label="ปีงบประมาณ" :error="errors.fiscal_year">
          <Select
            v-model="fiscalYear"
            label-id="plan-year"
            :options="yearOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือก --"
            :invalid="!!errors.fiscal_year"
            :aria-describedby="errors.fiscal_year ? 'plan-year-error' : undefined"
            fluid
          />
        </FormField>

        <label v-if="editingId" class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isActive" binary input-id="plan-is-active" />
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

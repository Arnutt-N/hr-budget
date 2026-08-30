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
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import type { Target, CreateTarget, UpdateTarget } from '@/types/target'
import {
  useTargetList,
  useCreateTarget,
  useUpdateTarget,
  useDeleteTarget,
} from '@/queries/useTargets'
import { useTargetTypeList } from '@/queries/useTargetTypes'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import { useOrganizationList } from '@/queries/useOrganizations'
import { useCategoryList } from '@/queries/useCategories'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

const { data: targets, isLoading, isError, error } = useTargetList()
const createMutation = useCreateTarget()
const updateMutation = useUpdateTarget()
const deleteMutation = useDeleteTarget()

// Dropdown data sources
const { data: targetTypes } = useTargetTypeList()
const { data: fiscalYears } = useFiscalYearList()
const { data: organizations } = useOrganizationList()
const { data: categories } = useCategoryList()

// Option arrays for Selects
const targetTypeOptions = computed(() =>
  (targetTypes.value ?? []).map((t) => ({ value: t.id, label: t.name_th })),
)
const fiscalYearOptions = computed(() =>
  (fiscalYears.value ?? []).map((fy) => ({ value: fy.year, label: String(fy.year) })),
)
const organizationOptions = computed(() =>
  (organizations.value ?? []).map((o) => ({ value: o.id, label: o.name_th })),
)
const categoryOptions = computed(() =>
  (categories.value ?? []).map((c) => ({ value: c.id, label: c.name_th })),
)
const quarterOptions = [
  { value: null, label: 'ทั้งปี' },
  { value: 1, label: 'ไตรมาส 1' },
  { value: 2, label: 'ไตรมาส 2' },
  { value: 3, label: 'ไตรมาส 3' },
  { value: 4, label: 'ไตรมาส 4' },
]

// id -> name_th map for rendering the list column
const targetTypeMap = computed<Record<number, string>>(() => {
  const map: Record<number, string> = {}
  for (const t of targetTypes.value ?? []) map[t.id] = t.name_th
  return map
})

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขเป้าหมาย' : 'เพิ่มเป้าหมาย'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    target_type_id: z.coerce
      .number({ invalid_type_error: 'กรุณาเลือกประเภทเป้าหมาย', required_error: 'กรุณาเลือกประเภทเป้าหมาย' })
      .int()
      .min(1, 'กรุณาเลือกประเภทเป้าหมาย'),
    fiscal_year: z.coerce
      .number({ invalid_type_error: 'กรุณาเลือกปีงบประมาณ', required_error: 'กรุณาเลือกปีงบประมาณ' })
      .int()
      .min(2400, 'กรุณาเลือกปีงบประมาณ'),
    quarter: z.number().nullable().optional(),
    organization_id: z.number().nullable().optional(),
    category_id: z.number().nullable().optional(),
    target_percent: z.number().nullable().optional(),
    target_amount: z.number().nullable().optional(),
    notes: z.string().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [targetTypeId] = defineField('target_type_id')
const [fiscalYear] = defineField('fiscal_year')
const [quarter] = defineField('quarter')
const [organizationId] = defineField('organization_id')
const [categoryId] = defineField('category_id')
const [targetPercent] = defineField('target_percent')
const [targetAmount] = defineField('target_amount')
const [notes] = defineField('notes')

function defaultFiscalYear(): number {
  const current = (fiscalYears.value ?? []).find((fy) => fy.is_current)
  return current ? current.year : new Date().getFullYear() + 543
}

function openCreate(): void {
  editingId.value = null
  resetForm({
    values: {
      target_type_id: undefined,
      fiscal_year: defaultFiscalYear(),
      quarter: null,
      organization_id: null,
      category_id: null,
      target_percent: null,
      target_amount: null,
      notes: '',
    },
  })
  showDialog.value = true
}

function openEdit(target: Target): void {
  editingId.value = target.id
  resetForm({
    values: {
      target_type_id: target.target_type_id,
      fiscal_year: target.fiscal_year,
      quarter: target.quarter,
      organization_id: target.organization_id,
      category_id: target.category_id,
      target_percent: target.target_percent != null ? Number(target.target_percent) : null,
      target_amount: target.target_amount != null ? Number(target.target_amount) : null,
      notes: target.notes ?? '',
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  // zod coerces these to number; nullable selects pass null through.
  const payload: CreateTarget = {
    target_type_id: values.target_type_id,
    fiscal_year: values.fiscal_year,
    quarter: values.quarter ?? null,
    organization_id: values.organization_id ?? null,
    category_id: values.category_id ?? null,
    target_percent: values.target_percent ?? null,
    target_amount: values.target_amount ?? null,
    notes: values.notes ? values.notes : undefined,
  }

  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: payload as UpdateTarget })
      toast.add({ severity: 'success', summary: 'แก้ไขเป้าหมายสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(payload)
      toast.add({ severity: 'success', summary: 'เพิ่มเป้าหมายสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function onDelete(target: Target): void {
  confirmDelete({
    message: `ยืนยันลบเป้าหมายนี้?`,
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(target.id)
        toast.add({ severity: 'success', summary: 'ลบเป้าหมายสำเร็จ', life: 3000 })
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
    <PageHeader title="เป้าหมายงบประมาณ">
      <Button label="เพิ่มเป้าหมาย" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <DataTable
      v-else
      :value="targets ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลเป้าหมาย" />
      </template>

      <Column header="ประเภทเป้าหมาย">
        <template #body="{ data }">
          <span class="font-medium">{{ targetTypeMap[data.target_type_id] ?? data.target_type_id }}</span>
        </template>
      </Column>
      <Column field="fiscal_year" header="ปีงบประมาณ" sortable />
      <Column header="ไตรมาส">
        <template #body="{ data }">
          {{ data.quarter ? `ไตรมาส ${data.quarter}` : 'ทั้งปี' }}
        </template>
      </Column>
      <Column header="เป้าหมาย (%)">
        <template #body="{ data }">
          {{ data.target_percent != null ? data.target_percent + '%' : '-' }}
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
        <FormField id="tgt-type" labelled-by label="ประเภทเป้าหมาย" :error="errors.target_type_id">
          <Select
            v-model="targetTypeId"
            aria-labelledby="tgt-type-label"
            :options="targetTypeOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือกประเภทเป้าหมาย --"
            :invalid="!!errors.target_type_id"
            :aria-describedby="errors.target_type_id ? 'tgt-type-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="tgt-year" labelled-by label="ปีงบประมาณ" :error="errors.fiscal_year">
          <Select
            v-model="fiscalYear"
            aria-labelledby="tgt-year-label"
            :options="fiscalYearOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือกปีงบประมาณ --"
            :invalid="!!errors.fiscal_year"
            :aria-describedby="errors.fiscal_year ? 'tgt-year-error' : undefined"
            fluid
          />
        </FormField>

        <FormField id="tgt-quarter" labelled-by label="ไตรมาส">
          <Select
            v-model="quarter"
            aria-labelledby="tgt-quarter-label"
            :options="quarterOptions"
            option-label="label"
            option-value="value"
            placeholder="ทั้งปี"
            show-clear
            fluid
          />
        </FormField>

        <FormField id="tgt-org" labelled-by label="หน่วยงาน">
          <Select
            v-model="organizationId"
            aria-labelledby="tgt-org-label"
            :options="organizationOptions"
            option-label="label"
            option-value="value"
            placeholder="ทุกหน่วยงาน"
            show-clear
            fluid
          />
        </FormField>

        <FormField id="tgt-cat" labelled-by label="หมวดงบประมาณ">
          <Select
            v-model="categoryId"
            aria-labelledby="tgt-cat-label"
            :options="categoryOptions"
            option-label="label"
            option-value="value"
            placeholder="ทุกหมวด"
            show-clear
            fluid
          />
        </FormField>

        <FormField id="tgt-percent" label="เป้าหมาย (%)">
          <InputNumber
            v-model="targetPercent"
            input-id="tgt-percent"
            suffix="%"
            :min="0"
            :max="100"
            fluid
          />
        </FormField>

        <FormField id="tgt-amount" label="เป้าหมาย (บาท)">
          <InputNumber
            v-model="targetAmount"
            input-id="tgt-amount"
            :min-fraction-digits="2"
            fluid
          />
        </FormField>

        <FormField id="tgt-notes" label="หมายเหตุ">
          <Textarea id="tgt-notes" v-model="notes" rows="2" fluid />
        </FormField>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

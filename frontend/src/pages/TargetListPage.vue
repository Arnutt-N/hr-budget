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
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Message from 'primevue/message'
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

const confirm = useConfirm()
const toast = useToast()

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

function confirmDelete(target: Target): void {
  confirm.require({
    message: `ยืนยันลบเป้าหมายนี้?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
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
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">เป้าหมายงบประมาณ</h1>
      <Button label="เพิ่มเป้าหมาย" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

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
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลเป้าหมาย</p>
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
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label id="tgt-type" class="text-sm font-medium text-dark-muted">ประเภทเป้าหมาย</label>
          <Select
            v-model="targetTypeId"
            label-id="tgt-type"
            :options="targetTypeOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือกประเภทเป้าหมาย --"
            :invalid="!!errors.target_type_id"
            fluid
          />
          <small v-if="errors.target_type_id" class="text-red-600" role="alert">{{ errors.target_type_id }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label id="tgt-year" class="text-sm font-medium text-dark-muted">ปีงบประมาณ</label>
          <Select
            v-model="fiscalYear"
            label-id="tgt-year"
            :options="fiscalYearOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือกปีงบประมาณ --"
            :invalid="!!errors.fiscal_year"
            fluid
          />
          <small v-if="errors.fiscal_year" class="text-red-600" role="alert">{{ errors.fiscal_year }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label id="tgt-quarter" class="text-sm font-medium text-dark-muted">ไตรมาส</label>
          <Select
            v-model="quarter"
            label-id="tgt-quarter"
            :options="quarterOptions"
            option-label="label"
            option-value="value"
            placeholder="ทั้งปี"
            show-clear
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label id="tgt-org" class="text-sm font-medium text-dark-muted">หน่วยงาน</label>
          <Select
            v-model="organizationId"
            label-id="tgt-org"
            :options="organizationOptions"
            option-label="label"
            option-value="value"
            placeholder="ทุกหน่วยงาน"
            show-clear
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label id="tgt-cat" class="text-sm font-medium text-dark-muted">หมวดงบประมาณ</label>
          <Select
            v-model="categoryId"
            label-id="tgt-cat"
            :options="categoryOptions"
            option-label="label"
            option-value="value"
            placeholder="ทุกหมวด"
            show-clear
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label for="tgt-percent" class="text-sm font-medium text-dark-muted">เป้าหมาย (%)</label>
          <InputNumber
            v-model="targetPercent"
            input-id="tgt-percent"
            suffix="%"
            :min="0"
            :max="100"
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label for="tgt-amount" class="text-sm font-medium text-dark-muted">เป้าหมาย (บาท)</label>
          <InputNumber
            v-model="targetAmount"
            input-id="tgt-amount"
            :min-fraction-digits="2"
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label for="tgt-notes" class="text-sm font-medium text-dark-muted">หมายเหตุ</label>
          <Textarea id="tgt-notes" v-model="notes" rows="2" fluid />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

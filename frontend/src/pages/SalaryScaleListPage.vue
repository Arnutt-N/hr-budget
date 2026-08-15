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
import Select from 'primevue/select'
import Message from 'primevue/message'
import { formatThaiDate } from '@/lib/date'
import type { SalaryScale } from '@/types/salary'
import type { EmployeeCategory } from '@/types/position'
import { useSalaryScaleList, useCreateSalaryScale, useDeleteSalaryScale } from '@/queries/useSalary'

const confirm = useConfirm()
const toast = useToast()

const { data: scales, isLoading, isError, error } = useSalaryScaleList()
const createMutation = useCreateSalaryScale()
const deleteMutation = useDeleteSalaryScale()

const CATEGORY_OPTIONS = [
  { value: 'civil_servant', label: 'ข้าราชการ' },
  { value: 'government_employee', label: 'พนักงานราชการ' },
  { value: 'permanent_employee', label: 'ลูกจ้างประจำ' },
]
function categoryLabel(v: string): string {
  return CATEGORY_OPTIONS.find((o) => o.value === v)?.label ?? v
}

const showDialog = ref(false)
const saving = computed(() => createMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    employee_category: z.string().min(1, 'กรุณาเลือกประเภทบุคลากร'),
    level_code: z.string({ required_error: 'กรุณากรอกระดับ' }).min(1, 'กรุณากรอกระดับ'),
    min_amount: z.coerce.number({ invalid_type_error: 'กรุณากรอกอัตราขั้นต่ำ' }).min(0),
    max_amount: z.coerce.number({ invalid_type_error: 'กรุณากรอกอัตราขั้นสูง' }).min(0),
    effective_from: z.string({ required_error: 'กรุณาเลือกวันเริ่มมีผล' }).min(1, 'กรุณาเลือกวันเริ่มมีผล'),
    doc_no: z.string().optional(),
  }),
)
const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [employeeCategory] = defineField('employee_category')
const [levelCode] = defineField('level_code')
const [minAmount] = defineField('min_amount')
const [maxAmount] = defineField('max_amount')
const [effectiveFrom] = defineField('effective_from')
const [docNo] = defineField('doc_no')

function openCreate(): void {
  resetForm({
    values: {
      employee_category: 'civil_servant',
      level_code: '',
      min_amount: 0,
      max_amount: 0,
      effective_from: '',
      doc_no: '',
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  if (values.max_amount < values.min_amount) {
    toast.add({ severity: 'error', summary: 'อัตราขั้นสูงต้องไม่ต่ำกว่าขั้นต่ำ', life: 5000 })
    return
  }
  try {
    await createMutation.mutateAsync({
      employee_category: values.employee_category as EmployeeCategory,
      level_code: values.level_code,
      min_amount: values.min_amount,
      max_amount: values.max_amount,
      effective_from: values.effective_from,
      doc_no: values.doc_no || null,
    })
    toast.add({ severity: 'success', summary: 'เพิ่มอัตราเงินเดือนสำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(s: SalaryScale): void {
  confirm.require({
    message: `ยืนยันลบอัตรา ${categoryLabel(s.employee_category)} ระดับ ${s.level_code}?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(s.id)
        toast.add({ severity: 'success', summary: 'ลบสำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">อัตราเงินเดือนขั้นต่ำ–ขั้นสูง</h1>
      <Button label="เพิ่มอัตรา" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <Message severity="info" :closable="false" class="mb-4">
      อัตราขั้นสูงคือเพดานตอนประมาณการเลื่อนเงินเดือน — ขาดข้อมูลตรงนี้ งบประมาณการจะสูงเกินจริงในกลุ่มอาวุโส
    </Message>

    <DataTable
      :value="scales ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลอัตราเงินเดือน</p>
      </template>

      <Column header="ประเภทบุคลากร">
        <template #body="{ data }">{{ categoryLabel(data.employee_category) }}</template>
      </Column>
      <Column field="level_code" header="ระดับ" sortable />
      <Column header="ขั้นต่ำ">
        <template #body="{ data }">{{ Number(data.min_amount).toLocaleString('th-TH') }}</template>
      </Column>
      <Column header="ขั้นสูง (เพดาน)">
        <template #body="{ data }">{{ Number(data.max_amount).toLocaleString('th-TH') }}</template>
      </Column>
      <Column header="ช่วงมีผล">
        <template #body="{ data }">
          {{ formatThaiDate(data.effective_from) }} — {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
        </template>
      </Column>
      <Column field="doc_no" header="เอกสาร">
        <template #body="{ data }">{{ data.doc_no ?? '—' }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" header="เพิ่มอัตราเงินเดือน" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">ประเภทบุคลากร</span>
          <Select
            v-model="employeeCategory"
            :options="CATEGORY_OPTIONS"
            option-label="label"
            option-value="value"
            :invalid="!!errors.employee_category"
            fluid
          />
          <small v-if="errors.employee_category" class="text-red-600" role="alert">{{ errors.employee_category }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">ระดับ</span>
          <InputText v-model="levelCode" :invalid="!!errors.level_code" fluid />
          <small v-if="errors.level_code" class="text-red-600" role="alert">{{ errors.level_code }}</small>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">ขั้นต่ำ</span>
            <InputNumber v-model="minAmount" :min="0" :invalid="!!errors.min_amount" fluid />
            <small v-if="errors.min_amount" class="text-red-600" role="alert">{{ errors.min_amount }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">ขั้นสูง (เพดาน)</span>
            <InputNumber v-model="maxAmount" :min="0" :invalid="!!errors.max_amount" fluid />
            <small v-if="errors.max_amount" class="text-red-600" role="alert">{{ errors.max_amount }}</small>
          </div>
        </div>

        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">วันเริ่มมีผล</span>
          <InputText v-model="effectiveFrom" type="date" :invalid="!!errors.effective_from" fluid />
          <small v-if="errors.effective_from" class="text-red-600" role="alert">{{ errors.effective_from }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">เลขที่เอกสาร</span>
          <InputText v-model="docNo" fluid />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

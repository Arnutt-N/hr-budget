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
import Select from 'primevue/select'
import Message from 'primevue/message'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { formatThaiDate } from '@/lib/date'
import type { SalaryScale } from '@/types/salary'
import type { EmployeeCategory } from '@/types/position'
import { CATEGORY_OPTIONS, categoryLabel } from '@/lib/personnel'
import { useSalaryScaleList, useCreateSalaryScale, useDeleteSalaryScale } from '@/queries/useSalary'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

const { data: scales, isLoading, isError, error } = useSalaryScaleList()
const createMutation = useCreateSalaryScale()
const deleteMutation = useDeleteSalaryScale()

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

function onDelete(s: SalaryScale): void {
  confirmDelete({
    message: `ยืนยันลบอัตรา ${categoryLabel(s.employee_category)} ระดับ ${s.level_code}?`,
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
    <PageHeader title="อัตราเงินเดือนขั้นต่ำ–ขั้นสูง">
      <Button label="เพิ่มอัตรา" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

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
        <ListEmptyState message="ยังไม่มีข้อมูลอัตราเงินเดือน" />
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
          <Button label="ลบ" size="small" text severity="danger" @click="onDelete(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" header="เพิ่มอัตราเงินเดือน" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label id="ss-category" class="text-sm font-medium text-dark-muted">ประเภทบุคลากร</label>
          <Select
            v-model="employeeCategory"
            label-id="ss-category"
            :options="CATEGORY_OPTIONS"
            option-label="label"
            option-value="value"
            :invalid="!!errors.employee_category"
            :aria-describedby="errors.employee_category ? 'ss-category-error' : undefined"
            fluid
          />
          <small v-if="errors.employee_category" id="ss-category-error" class="text-red-400" role="alert">{{ errors.employee_category }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="ss-level" class="text-sm font-medium text-dark-muted">ระดับ</label>
          <InputText
            id="ss-level"
            v-model="levelCode"
            :invalid="!!errors.level_code"
            :aria-describedby="errors.level_code ? 'ss-level-error' : undefined"
            fluid
          />
          <small v-if="errors.level_code" id="ss-level-error" class="text-red-400" role="alert">{{ errors.level_code }}</small>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label for="ss-min" class="text-sm font-medium text-dark-muted">ขั้นต่ำ</label>
            <InputNumber
              v-model="minAmount"
              input-id="ss-min"
              :min="0"
              :invalid="!!errors.min_amount"
              :aria-describedby="errors.min_amount ? 'ss-min-error' : undefined"
              fluid
            />
            <small v-if="errors.min_amount" id="ss-min-error" class="text-red-400" role="alert">{{ errors.min_amount }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="ss-max" class="text-sm font-medium text-dark-muted">ขั้นสูง (เพดาน)</label>
            <InputNumber
              v-model="maxAmount"
              input-id="ss-max"
              :min="0"
              :invalid="!!errors.max_amount"
              :aria-describedby="errors.max_amount ? 'ss-max-error' : undefined"
              fluid
            />
            <small v-if="errors.max_amount" id="ss-max-error" class="text-red-400" role="alert">{{ errors.max_amount }}</small>
          </div>
        </div>

        <div class="flex flex-col gap-1">
          <label for="ss-effective" class="text-sm font-medium text-dark-muted">วันเริ่มมีผล</label>
          <InputText
            id="ss-effective"
            v-model="effectiveFrom"
            type="date"
            :invalid="!!errors.effective_from"
            :aria-describedby="errors.effective_from ? 'ss-effective-error' : undefined"
            fluid
          />
          <small v-if="errors.effective_from" id="ss-effective-error" class="text-red-400" role="alert">{{ errors.effective_from }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="ss-doc" class="text-sm font-medium text-dark-muted">เลขที่เอกสาร</label>
          <InputText id="ss-doc" v-model="docNo" fluid />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

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
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import { formatThaiDate } from '@/lib/date'
import type { FiscalYear } from '@/types/fiscal-year'
import {
  useFiscalYearList,
  useCreateFiscalYear,
  useUpdateFiscalYear,
  useDeleteFiscalYear,
  useSetCurrentFiscalYear,
} from '@/queries/useFiscalYears'

const confirm = useConfirm()
const toast = useToast()

const { data: fiscalYears, isLoading, isError, error } = useFiscalYearList()
const createMutation = useCreateFiscalYear()
const updateMutation = useUpdateFiscalYear()
const deleteMutation = useDeleteFiscalYear()
const setCurrentMutation = useSetCurrentFiscalYear()

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขปีงบประมาณ' : 'เพิ่มปีงบประมาณ'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    year: z.coerce
      .number({ invalid_type_error: 'กรุณากรอกปีงบประมาณ' })
      .int()
      .min(2400, 'ปี พ.ศ. ไม่ถูกต้อง')
      .max(2700, 'ปี พ.ศ. ไม่ถูกต้อง'),
    start_date: z.string({ required_error: 'กรุณาเลือกวันเริ่มต้น' }).min(1, 'กรุณาเลือกวันเริ่มต้น'),
    end_date: z.string({ required_error: 'กรุณาเลือกวันสิ้นสุด' }).min(1, 'กรุณาเลือกวันสิ้นสุด'),
    is_current: z.boolean().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [year] = defineField('year')
const [startDate] = defineField('start_date')
const [endDate] = defineField('end_date')
const [isCurrent] = defineField('is_current')

function openCreate(): void {
  editingId.value = null
  resetForm({
    values: {
      year: new Date().getFullYear() + 543,
      start_date: '',
      end_date: '',
      is_current: false,
    },
  })
  showDialog.value = true
}

function openEdit(fy: FiscalYear): void {
  editingId.value = fy.id
  resetForm({
    values: {
      year: fy.year,
      start_date: fy.start_date,
      end_date: fy.end_date,
      is_current: !!fy.is_current,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขปีงบประมาณสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มปีงบประมาณสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(fy: FiscalYear): void {
  confirm.require({
    message: `ยืนยันลบปีงบประมาณ ${fy.year}?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(fy.id)
        toast.add({ severity: 'success', summary: 'ลบปีงบประมาณสำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

function confirmSetCurrent(fy: FiscalYear): void {
  confirm.require({
    message: `ตั้งปีงบประมาณ ${fy.year} เป็นปีปัจจุบัน?`,
    header: 'ยืนยันการตั้งปีปัจจุบัน',
    icon: 'pi pi-calendar',
    acceptLabel: 'ยืนยัน',
    rejectLabel: 'ยกเลิก',
    accept: async () => {
      try {
        await setCurrentMutation.mutateAsync(fy.id)
        toast.add({ severity: 'success', summary: `ตั้งปี ${fy.year} เป็นปีปัจจุบันแล้ว`, life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ตั้งปีปัจจุบันไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

function statusOf(fy: FiscalYear): { label: string; severity: string } {
  if (fy.is_current) return { label: 'ปีปัจจุบัน', severity: 'success' }
  if (fy.is_closed) return { label: 'ปิดแล้ว', severity: 'secondary' }
  return { label: 'เปิดใช้', severity: 'info' }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">ปีงบประมาณ</h1>
      <Button label="เพิ่มปีงบประมาณ" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="fiscalYears ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      sort-field="year"
      :sort-order="-1"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลปีงบประมาณ</p>
      </template>

      <Column field="year" header="ปีงบประมาณ" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.year }}</span>
        </template>
      </Column>
      <Column field="start_date" header="วันเริ่มต้น" sortable>
        <template #body="{ data }">{{ formatThaiDate(data.start_date) }}</template>
      </Column>
      <Column field="end_date" header="วันสิ้นสุด" sortable>
        <template #body="{ data }">{{ formatThaiDate(data.end_date) }}</template>
      </Column>
      <Column header="สถานะ">
        <template #body="{ data }">
          <Tag :value="statusOf(data).label" :severity="statusOf(data).severity" />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button
              v-if="!data.is_current"
              label="ตั้งเป็นปีปัจจุบัน"
              size="small"
              text
              severity="success"
              @click="confirmSetCurrent(data)"
            />
            <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label for="fy-year" class="text-sm font-medium text-dark-muted">ปีงบประมาณ (พ.ศ.)</label>
          <InputNumber
            v-model="year"
            input-id="fy-year"
            :use-grouping="false"
            :invalid="!!errors.year"
            fluid
          />
          <small v-if="errors.year" class="text-red-600" role="alert">{{ errors.year }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="fy-start" class="text-sm font-medium text-dark-muted">วันเริ่มต้น</label>
          <InputText id="fy-start" v-model="startDate" type="date" :invalid="!!errors.start_date" fluid />
          <small v-if="errors.start_date" class="text-red-600" role="alert">{{ errors.start_date }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="fy-end" class="text-sm font-medium text-dark-muted">วันสิ้นสุด</label>
          <InputText id="fy-end" v-model="endDate" type="date" :invalid="!!errors.end_date" fluid />
          <small v-if="errors.end_date" class="text-red-600" role="alert">{{ errors.end_date }}</small>
        </div>

        <label class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isCurrent" binary input-id="fy-is-current" />
          ตั้งเป็นปีงบประมาณปัจจุบัน
        </label>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

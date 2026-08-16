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
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import { formatThaiDate } from '@/lib/date'
import type { Position, EmployeeCategory } from '@/types/position'
import {
  usePositionList,
  useCreatePosition,
  useUpdatePosition,
  useDeletePosition,
  usePositionVersions,
  useCreatePositionVersion,
} from '@/queries/usePositions'
import { useOrganizationList } from '@/queries/useOrganizations'
import {
  usePositionAllowances,
  useCreatePositionAllowance,
  useDeletePositionAllowance,
} from '@/queries/usePersonnel'
import { useAllowanceTypeList } from '@/queries/useAllowances'
import { CATEGORY_OPTIONS, OCCUPANCY_OPTIONS, categoryLabel, occupancyTag } from '@/lib/personnel'
import type { PositionFilters } from '@/api/positions'

const confirm = useConfirm()
const toast = useToast()

const filters = ref<PositionFilters>({})
const { data: positions, isLoading, isError, error } = usePositionList(filters)
const { data: organizations } = useOrganizationList()
const createMutation = useCreatePosition()
const updateMutation = useUpdatePosition()
const deleteMutation = useDeletePosition()

// ---------- create/edit dialog ----------
const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขอัตรากำลัง' : 'เพิ่มอัตรากำลัง'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    pay_no: z.string({ required_error: 'กรุณากรอกเลขถือจ่าย' }).min(1, 'กรุณากรอกเลขถือจ่าย'),
    employee_category: z.string().min(1, 'กรุณาเลือกประเภทบุคลากร'),
    created_doc_no: z.string().optional(),
    organization_id: z.coerce.number({ invalid_type_error: 'กรุณาเลือกหน่วยงาน' }).int().min(1, 'กรุณาเลือกหน่วยงาน'),
    pos_no: z.string().optional(),
    level_code: z.string().optional(),
    base_salary: z.coerce.number({ invalid_type_error: 'กรุณากรอกเงินเดือน' }).min(0, 'เงินเดือนต้องไม่ติดลบ'),
    occupancy: z.string().min(1, 'กรุณาเลือกสถานะการครอง'),
    months_counted: z.coerce.number().int().min(1, '1-12').max(12, '1-12'),
    effective_from: z.string({ required_error: 'กรุณาเลือกวันเริ่มมีผล' }).min(1, 'กรุณาเลือกวันเริ่มมีผล'),
  }),
)
const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [payNo] = defineField('pay_no')
const [employeeCategory] = defineField('employee_category')
const [createdDocNo] = defineField('created_doc_no')
const [organizationId] = defineField('organization_id')
const [posNo] = defineField('pos_no')
const [levelCode] = defineField('level_code')
const [baseSalary] = defineField('base_salary')
const [occupancy] = defineField('occupancy')
const [monthsCounted] = defineField('months_counted')
const [effectiveFrom] = defineField('effective_from')

function openCreate(): void {
  editingId.value = null
  resetForm({
    values: {
      pay_no: '',
      employee_category: 'civil_servant',
      created_doc_no: '',
      organization_id: 0,
      pos_no: '',
      level_code: '',
      base_salary: 0,
      occupancy: 'occupied',
      months_counted: 12,
      effective_from: '',
    },
  })
  showDialog.value = true
}

function openEdit(p: Position): void {
  editingId.value = p.id
  resetForm({
    values: {
      pay_no: p.pay_no,
      employee_category: p.employee_category,
      created_doc_no: p.created_doc_no ?? '',
      organization_id: p.organization_id ?? 0,
      pos_no: p.pos_no ?? '',
      level_code: p.level_code ?? '',
      base_salary: p.base_salary ?? 0,
      occupancy: p.occupancy ?? 'occupied',
      months_counted: p.months_counted ?? 12,
      effective_from: p.effective_from ?? '',
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({
        id: editingId.value,
        data: {
          pay_no: values.pay_no,
          employee_category: values.employee_category as EmployeeCategory,
          created_doc_no: values.created_doc_no || null,
        },
      })
      toast.add({ severity: 'success', summary: 'แก้ไขอัตรากำลังสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync({
        pay_no: values.pay_no,
        employee_category: values.employee_category as EmployeeCategory,
        created_doc_no: values.created_doc_no || null,
        organization_id: values.organization_id,
        pos_no: values.pos_no || null,
        level_code: values.level_code || null,
        base_salary: values.base_salary,
        occupancy: values.occupancy as Position['occupancy'] & string,
        months_counted: values.months_counted,
        effective_from: values.effective_from,
      })
      toast.add({ severity: 'success', summary: 'เพิ่มอัตรากำลังสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(p: Position): void {
  confirm.require({
    message: `ยืนยันลบอัตราเลขถือจ่าย ${p.pay_no}?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(p.id)
        toast.add({ severity: 'success', summary: 'ลบอัตรากำลังสำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

// ---------- versions dialog ----------
const showVersions = ref(false)
const activePositionId = ref<number | null>(null)
const { data: versions, isLoading: versionsLoading } = usePositionVersions(activePositionId)
const createVersionMutation = useCreatePositionVersion()

const versionForm = ref({
  organization_id: 0,
  pos_no: '',
  level_code: '',
  base_salary: 0,
  salary_basis: 'estimated' as 'actual' | 'estimated',
  occupancy: 'occupied',
  months_counted: 12,
  approval_status: 'approved' as 'approved' | 'requested',
  effective_from: '',
  order_doc_no: '',
})

function openVersions(p: Position): void {
  activePositionId.value = p.id
  versionForm.value = {
    organization_id: p.organization_id ?? 0,
    pos_no: p.pos_no ?? '',
    level_code: p.level_code ?? '',
    base_salary: p.base_salary ?? 0,
    salary_basis: 'estimated',
    occupancy: p.occupancy ?? 'occupied',
    months_counted: p.months_counted ?? 12,
    approval_status: 'approved',
    effective_from: '',
    order_doc_no: '',
  }
  showVersions.value = true
}

async function onAddVersion(): Promise<void> {
  if (!activePositionId.value || !versionForm.value.effective_from) return
  try {
    await createVersionMutation.mutateAsync({
      id: activePositionId.value,
      data: {
        organization_id: versionForm.value.organization_id,
        pos_no: versionForm.value.pos_no || null,
        level_code: versionForm.value.level_code || null,
        base_salary: versionForm.value.base_salary,
        salary_basis: versionForm.value.salary_basis,
        occupancy: versionForm.value.occupancy as Position['occupancy'] & string,
        lifecycle: 'active',
        months_counted: versionForm.value.months_counted,
        approval_status: versionForm.value.approval_status,
        effective_from: versionForm.value.effective_from,
        order_doc_no: versionForm.value.order_doc_no || null,
      },
    })
    toast.add({ severity: 'success', summary: 'เพิ่มเวอร์ชันสำเร็จ (เวอร์ชันเดิมถูกปิดอัตโนมัติ)', life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'เพิ่มเวอร์ชันไม่สำเร็จ', detail: message, life: 5000 })
  }
}

// ---------- allowances dialog ----------
const showAllowances = ref(false)
const allowancePositionId = ref<number | null>(null)
const { data: allowances, isLoading: allowancesLoading } = usePositionAllowances(allowancePositionId)
const createAllowanceMutation = useCreatePositionAllowance()
const deleteAllowanceMutation = useDeletePositionAllowance()
const { data: allowanceTypes } = useAllowanceTypeList()

const allowanceForm = ref({
  allowance_type_id: 0,
  effective_from: '',
  doc_no: '',
})

function openAllowances(p: Position): void {
  allowancePositionId.value = p.id
  allowanceForm.value = { allowance_type_id: 0, effective_from: '', doc_no: '' }
  showAllowances.value = true
}

async function onAddAllowance(): Promise<void> {
  if (!allowancePositionId.value || !allowanceForm.value.allowance_type_id || !allowanceForm.value.effective_from) return
  try {
    await createAllowanceMutation.mutateAsync({
      positionId: allowancePositionId.value,
      data: {
        allowance_type_id: allowanceForm.value.allowance_type_id,
        effective_from: allowanceForm.value.effective_from,
        doc_no: allowanceForm.value.doc_no || null,
      },
    })
    toast.add({ severity: 'success', summary: 'เพิ่มสิทธิ์สำเร็จ', life: 3000 })
    allowanceForm.value = { allowance_type_id: 0, effective_from: '', doc_no: '' }
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'เพิ่มสิทธิ์ไม่สำเร็จ', detail: message, life: 5000 })
  }
}

function confirmDeleteAllowance(allowanceId: number): void {
  confirm.require({
    message: 'ลบสิทธิ์เงินเพิ่มนี้?',
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      if (!allowancePositionId.value) return
      try {
        await deleteAllowanceMutation.mutateAsync({ positionId: allowancePositionId.value, allowanceId })
        toast.add({ severity: 'success', summary: 'ลบสิทธิ์สำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">อัตรากำลัง</h1>
      <Button label="เพิ่มอัตรากำลัง" icon="pi pi-plus" @click="openCreate" />
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
      <Select
        v-model="filters.employee_category"
        :options="CATEGORY_OPTIONS"
        option-label="label"
        option-value="value"
        placeholder="ประเภทบุคลากร (ทั้งหมด)"
        show-clear
        class="w-52"
      />
      <Select
        v-model="filters.occupancy"
        :options="OCCUPANCY_OPTIONS"
        option-label="label"
        option-value="value"
        placeholder="สถานะการครอง (ทั้งหมด)"
        show-clear
        class="w-52"
      />
      <InputText v-model="filters.q" placeholder="ค้นหาเลขถือจ่าย / เลขที่ตำแหน่ง" class="w-72" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="positions ?? []"
      :loading="isLoading"
      paginator
      :rows="15"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลอัตรากำลัง</p>
      </template>

      <Column field="pay_no" header="เลขถือจ่าย" sortable />
      <Column field="pos_no" header="เลขที่ตำแหน่ง">
        <template #body="{ data }">{{ data.pos_no ?? '—' }}</template>
      </Column>
      <Column header="หน่วยงาน (เจ้าของงบ)">
        <template #body="{ data }">{{ data.organization_name ?? '—' }}</template>
      </Column>
      <Column header="ประเภท">
        <template #body="{ data }">{{ categoryLabel(data.employee_category) }}</template>
      </Column>
      <Column field="level_code" header="ระดับ">
        <template #body="{ data }">{{ data.level_code ?? '—' }}</template>
      </Column>
      <Column header="เงินเดือน" sortable field="base_salary">
        <template #body="{ data }">
          <span v-if="data.base_salary !== null">
            {{ Number(data.base_salary).toLocaleString('th-TH') }}
            <Tag
              v-if="data.salary_basis === 'estimated'"
              value="ประมาณ"
              severity="warn"
              class="ml-1"
              title="สถานะเงินเดือน: ประมาณการ (ยังไม่ยืนยันการเลื่อน)"
            />
          </span>
          <span v-else>—</span>
        </template>
      </Column>
      <Column header="สถานะการครอง">
        <template #body="{ data }">
          <Tag :value="occupancyTag(data.occupancy).label" :severity="occupancyTag(data.occupancy).severity" />
        </template>
      </Column>
      <Column header="เดือนที่นับ" field="months_counted">
        <template #body="{ data }">{{ data.months_counted ?? '—' }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button label="สิทธิ์" size="small" text severity="warn" @click="openAllowances(data)" />
            <Button label="เวอร์ชัน" size="small" text severity="info" @click="openVersions(data)" />
            <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- Create/Edit dialog -->
    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-lg">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">เลขถือจ่าย</label>
            <InputText v-model="payNo" :invalid="!!errors.pay_no" fluid />
            <small v-if="errors.pay_no" class="text-red-600" role="alert">{{ errors.pay_no }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">เลขที่ตำแหน่ง</label>
            <InputText v-model="posNo" fluid :disabled="!!editingId" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">ประเภทบุคลากร</label>
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
            <label class="text-sm font-medium text-dark-muted">หน่วยงานเจ้าของงบ</label>
            <Select
              v-model="organizationId"
              :options="organizations ?? []"
              option-label="name_th"
              option-value="id"
              :invalid="!!errors.organization_id"
              :disabled="!!editingId"
              fluid
              filter
            />
            <small v-if="errors.organization_id" class="text-red-600" role="alert">{{ errors.organization_id }}</small>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">ระดับ</label>
            <InputText v-model="levelCode" fluid :disabled="!!editingId" />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">เงินเดือน</label>
            <InputNumber v-model="baseSalary" :min="0" :invalid="!!errors.base_salary" fluid :disabled="!!editingId" />
            <small v-if="errors.base_salary" class="text-red-600" role="alert">{{ errors.base_salary }}</small>
          </div>
        </div>

        <div v-if="!editingId" class="grid grid-cols-3 gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">สถานะการครอง</label>
            <Select v-model="occupancy" :options="OCCUPANCY_OPTIONS" option-label="label" option-value="value" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">เดือนที่นับ (1-12)</label>
            <InputNumber v-model="monthsCounted" :min="1" :max="12" :invalid="!!errors.months_counted" fluid />
            <small v-if="errors.months_counted" class="text-red-600" role="alert">{{ errors.months_counted }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-dark-muted">วันเริ่มมีผล</label>
            <InputText v-model="effectiveFrom" type="date" :invalid="!!errors.effective_from" fluid />
            <small v-if="errors.effective_from" class="text-red-600" role="alert">{{ errors.effective_from }}</small>
          </div>
        </div>
        <p v-else class="text-xs text-dark-muted">
          แก้เฉพาะเลขถือจ่าย/ประเภท/คำสั่งตั้งอัตรา — การเปลี่ยนเงินเดือน/ระดับ/หน่วยงาน ให้เพิ่ม "เวอร์ชัน" ใหม่แทน
        </p>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-dark-muted">เลขที่คำสั่งตั้งอัตรา</label>
          <InputText v-model="createdDocNo" fluid />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>

    <!-- Versions dialog -->
    <Dialog v-model:visible="showVersions" header="เวอร์ชันของอัตรา (เรียงใหม่สุดก่อน)" modal class="w-full max-w-3xl">
      <DataTable :value="versions ?? []" :loading="versionsLoading" data-key="id">
        <template #empty>
          <p class="py-3 text-center text-dark-muted">ยังไม่มีเวอร์ชัน</p>
        </template>
        <Column header="ช่วงมีผล">
          <template #body="{ data }">
            {{ formatThaiDate(data.effective_from) }} — {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
          </template>
        </Column>
        <Column field="level_code" header="ระดับ">
          <template #body="{ data }">{{ data.level_code ?? '—' }}</template>
        </Column>
        <Column field="base_salary" header="เงินเดือน">
          <template #body="{ data }">{{ Number(data.base_salary).toLocaleString('th-TH') }}</template>
        </Column>
        <Column header="สถานะเงินเดือน">
          <template #body="{ data }">
            <Tag :value="data.salary_basis === 'actual' ? 'ยืนยัน' : 'ประมาณ'" :severity="data.salary_basis === 'actual' ? 'success' : 'warn'" />
          </template>
        </Column>
        <Column header="การครอง">
          <template #body="{ data }">
            <Tag :value="occupancyTag(data.occupancy).label" :severity="occupancyTag(data.occupancy).severity" />
          </template>
        </Column>
        <Column field="months_counted" header="เดือน" />
        <Column field="order_doc_no" header="คำสั่ง">
          <template #body="{ data }">{{ data.order_doc_no ?? '—' }}</template>
        </Column>
      </DataTable>

      <div class="mt-4 rounded-lg border border-dark-border p-4">
        <h3 class="mb-3 font-semibold text-white">เพิ่มเวอร์ชันใหม่ (ปิดเวอร์ชันเดิมอัตโนมัติ)</h3>
        <div class="grid grid-cols-3 gap-3">
          <InputText v-model="versionForm.effective_from" type="date" placeholder="วันเริ่มมีผล" />
          <InputNumber v-model="versionForm.base_salary" :min="0" placeholder="เงินเดือน" fluid />
          <InputText v-model="versionForm.level_code" placeholder="ระดับ" />
          <Select
            v-model="versionForm.organization_id"
            :options="organizations ?? []"
            option-label="name_th"
            option-value="id"
            placeholder="หน่วยงาน"
            filter
            fluid
          />
          <Select v-model="versionForm.occupancy" :options="OCCUPANCY_OPTIONS" option-label="label" option-value="value" fluid />
          <InputNumber v-model="versionForm.months_counted" :min="1" :max="12" placeholder="เดือนที่นับ" fluid />
          <Select
            v-model="versionForm.salary_basis"
            :options="[{ value: 'estimated', label: 'ประมาณการ' }, { value: 'actual', label: 'ยืนยันแล้ว' }]"
            option-label="label"
            option-value="value"
            fluid
          />
          <Select
            v-model="versionForm.approval_status"
            :options="[{ value: 'approved', label: 'อนุมัติแล้ว' }, { value: 'requested', label: 'รออนุมัติ (ไม่นับงบ)' }]"
            option-label="label"
            option-value="value"
            fluid
          />
          <InputText v-model="versionForm.order_doc_no" placeholder="เลขที่คำสั่ง" />
        </div>
        <div class="mt-3 flex justify-end">
          <Button
            label="เพิ่มเวอร์ชัน"
            icon="pi pi-plus"
            :loading="createVersionMutation.isPending.value"
            :disabled="!versionForm.effective_from"
            @click="onAddVersion"
          />
        </div>
      </div>
    </Dialog>

    <!-- Allowances dialog -->
    <Dialog v-model:visible="showAllowances" header="สิทธิ์เงินเพิ่มของอัตรา" modal class="w-full max-w-2xl">
      <DataTable :value="allowances ?? []" :loading="allowancesLoading" data-key="id">
        <template #empty>
          <p class="py-3 text-center text-dark-muted">ยังไม่มีสิทธิ์ (ไม่มีแถว = ไม่มีสิทธิ์)</p>
        </template>
        <Column header="เงินเพิ่ม">
          <template #body="{ data }">{{ data.short_name ?? data.allowance_name ?? '—' }}</template>
        </Column>
        <Column header="ช่วงมีสิทธิ์">
          <template #body="{ data }">
            {{ formatThaiDate(data.effective_from) }} — {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
          </template>
        </Column>
        <Column field="doc_no" header="เอกสาร">
          <template #body="{ data }">{{ data.doc_no ?? '—' }}</template>
        </Column>
        <Column header="" class="text-right">
          <template #body="{ data }">
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDeleteAllowance(data.id)" />
          </template>
        </Column>
      </DataTable>

      <div class="mt-4 rounded-lg border border-dark-border p-4">
        <h3 class="mb-3 font-semibold text-white">เพิ่มสิทธิ์</h3>
        <div class="grid grid-cols-3 gap-3">
          <Select
            v-model="allowanceForm.allowance_type_id"
            :options="allowanceTypes ?? []"
            option-label="name_th"
            option-value="id"
            placeholder="ชนิดเงินเพิ่ม"
            filter
            fluid
          />
          <InputText v-model="allowanceForm.effective_from" type="date" placeholder="วันเริ่มมีสิทธิ์" />
          <InputText v-model="allowanceForm.doc_no" placeholder="เลขที่คำสั่ง" />
        </div>
        <div class="mt-3 flex justify-end">
          <Button
            label="เพิ่มสิทธิ์"
            icon="pi pi-plus"
            :loading="createAllowanceMutation.isPending.value"
            :disabled="!allowanceForm.allowance_type_id || !allowanceForm.effective_from"
            @click="onAddAllowance"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>

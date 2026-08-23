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
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
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

const confirmDeletePrompt = useDeleteConfirm()
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
  confirmDeletePrompt({
    message: `ยืนยันลบอัตราเลขถือจ่าย ${p.pay_no}?`,
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
const versionPosNo = ref('')
const { data: versions, isLoading: versionsLoading } = usePositionVersions(activePositionId)
const createVersionMutation = useCreatePositionVersion()

const versionSchema = toTypedSchema(
  z.object({
    effective_from: z.string({ required_error: 'กรุณาเลือกวันเริ่มมีผล' }).min(1, 'กรุณาเลือกวันเริ่มมีผล'),
    base_salary: z.coerce.number({ invalid_type_error: 'กรุณากรอกเงินเดือน' }).min(0, 'เงินเดือนต้องไม่ติดลบ'),
    level_code: z.string().optional(),
    organization_id: z.coerce.number({ invalid_type_error: 'กรุณาเลือกหน่วยงาน' }).int().min(1, 'กรุณาเลือกหน่วยงาน'),
    occupancy: z.string().min(1, 'กรุณาเลือกสถานะการครอง'),
    months_counted: z.coerce.number().int().min(1, '1-12').max(12, '1-12'),
    salary_basis: z.string().min(1, 'กรุณาเลือกสถานะเงินเดือน'),
    approval_status: z.string().min(1, 'กรุณาเลือกสถานะการอนุมัติ'),
    order_doc_no: z.string().optional(),
  }),
)
const {
  defineField: defineVersionField,
  handleSubmit: handleVersionSubmit,
  errors: versionErrors,
  resetForm: resetVersionForm,
} = useForm({ validationSchema: versionSchema })
const [vEffectiveFrom] = defineVersionField('effective_from')
const [vBaseSalary] = defineVersionField('base_salary')
const [vLevelCode] = defineVersionField('level_code')
const [vOrganizationId] = defineVersionField('organization_id')
const [vOccupancy] = defineVersionField('occupancy')
const [vMonthsCounted] = defineVersionField('months_counted')
const [vSalaryBasis] = defineVersionField('salary_basis')
const [vApprovalStatus] = defineVersionField('approval_status')
const [vOrderDocNo] = defineVersionField('order_doc_no')

function openVersions(p: Position): void {
  activePositionId.value = p.id
  versionPosNo.value = p.pos_no ?? ''
  resetVersionForm({
    values: {
      effective_from: '',
      base_salary: p.base_salary ?? 0,
      level_code: p.level_code ?? '',
      organization_id: p.organization_id ?? 0,
      occupancy: p.occupancy ?? 'occupied',
      months_counted: p.months_counted ?? 12,
      salary_basis: 'estimated',
      approval_status: 'approved',
      order_doc_no: '',
    },
  })
  showVersions.value = true
}

const onAddVersion = handleVersionSubmit(async (values) => {
  if (!activePositionId.value) return
  try {
    await createVersionMutation.mutateAsync({
      id: activePositionId.value,
      data: {
        organization_id: values.organization_id,
        pos_no: versionPosNo.value || null,
        level_code: values.level_code || null,
        base_salary: values.base_salary,
        salary_basis: values.salary_basis as 'actual' | 'estimated',
        occupancy: values.occupancy as Position['occupancy'] & string,
        lifecycle: 'active',
        months_counted: values.months_counted,
        approval_status: values.approval_status as 'approved' | 'requested',
        effective_from: values.effective_from,
        order_doc_no: values.order_doc_no || null,
      },
    })
    toast.add({ severity: 'success', summary: 'เพิ่มเวอร์ชันสำเร็จ (เวอร์ชันเดิมถูกปิดอัตโนมัติ)', life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'เพิ่มเวอร์ชันไม่สำเร็จ', detail: message, life: 5000 })
  }
})

// ---------- allowances dialog ----------
const showAllowances = ref(false)
const allowancePositionId = ref<number | null>(null)
const { data: allowances, isLoading: allowancesLoading } = usePositionAllowances(allowancePositionId)
const createAllowanceMutation = useCreatePositionAllowance()
const deleteAllowanceMutation = useDeletePositionAllowance()
const { data: allowanceTypes } = useAllowanceTypeList()

const allowanceSchema = toTypedSchema(
  z.object({
    allowance_type_id: z.coerce
      .number({ invalid_type_error: 'กรุณาเลือกชนิดเงินเพิ่ม' })
      .int()
      .min(1, 'กรุณาเลือกชนิดเงินเพิ่ม'),
    effective_from: z.string({ required_error: 'กรุณาเลือกวันเริ่มมีสิทธิ์' }).min(1, 'กรุณาเลือกวันเริ่มมีสิทธิ์'),
    doc_no: z.string().optional(),
  }),
)
const {
  defineField: defineAllowanceField,
  handleSubmit: handleAllowanceSubmit,
  errors: allowanceErrors,
  resetForm: resetAllowanceForm,
} = useForm({ validationSchema: allowanceSchema })
const [aTypeId] = defineAllowanceField('allowance_type_id')
const [aEffectiveFrom] = defineAllowanceField('effective_from')
const [aDocNo] = defineAllowanceField('doc_no')

function openAllowances(p: Position): void {
  allowancePositionId.value = p.id
  resetAllowanceForm({ values: { allowance_type_id: 0, effective_from: '', doc_no: '' } })
  showAllowances.value = true
}

const onAddAllowance = handleAllowanceSubmit(async (values) => {
  if (!allowancePositionId.value) return
  try {
    await createAllowanceMutation.mutateAsync({
      positionId: allowancePositionId.value,
      data: {
        allowance_type_id: values.allowance_type_id,
        effective_from: values.effective_from,
        doc_no: values.doc_no || null,
      },
    })
    toast.add({ severity: 'success', summary: 'เพิ่มสิทธิ์สำเร็จ', life: 3000 })
    resetAllowanceForm({ values: { allowance_type_id: 0, effective_from: '', doc_no: '' } })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'เพิ่มสิทธิ์ไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDeleteAllowance(allowanceId: number): void {
  confirmDeletePrompt({
    message: 'ลบสิทธิ์เงินเพิ่มนี้?',
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
    <PageHeader title="อัตรากำลัง">
      <Button label="เพิ่มอัตรากำลัง" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

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

    <QueryErrorState v-if="isError" :error="error" />

    <DataTable
      v-else
      :value="positions ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลอัตรากำลัง" />
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
            <label for="pos-pay-no" class="text-sm font-medium text-dark-muted">เลขถือจ่าย</label>
            <InputText
              id="pos-pay-no"
              v-model="payNo"
              :invalid="!!errors.pay_no"
              :aria-describedby="errors.pay_no ? 'pos-pay-no-error' : undefined"
              fluid
            />
            <small v-if="errors.pay_no" id="pos-pay-no-error" class="text-red-400" role="alert">{{ errors.pay_no }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="pos-pos-no" class="text-sm font-medium text-dark-muted">เลขที่ตำแหน่ง</label>
            <InputText id="pos-pos-no" v-model="posNo" fluid :disabled="!!editingId" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label id="pos-category" class="text-sm font-medium text-dark-muted">ประเภทบุคลากร</label>
            <Select
              v-model="employeeCategory"
              label-id="pos-category"
              :options="CATEGORY_OPTIONS"
              option-label="label"
              option-value="value"
              :invalid="!!errors.employee_category"
              :aria-describedby="errors.employee_category ? 'pos-category-error' : undefined"
              fluid
            />
            <small v-if="errors.employee_category" id="pos-category-error" class="text-red-400" role="alert">{{ errors.employee_category }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label id="pos-org" class="text-sm font-medium text-dark-muted">หน่วยงานเจ้าของงบ</label>
            <Select
              v-model="organizationId"
              label-id="pos-org"
              :options="organizations ?? []"
              option-label="name_th"
              option-value="id"
              :invalid="!!errors.organization_id"
              :aria-describedby="errors.organization_id ? 'pos-org-error' : undefined"
              :disabled="!!editingId"
              filter
              fluid
            />
            <small v-if="errors.organization_id" id="pos-org-error" class="text-red-400" role="alert">{{ errors.organization_id }}</small>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <label for="pos-level" class="text-sm font-medium text-dark-muted">ระดับ</label>
            <InputText id="pos-level" v-model="levelCode" fluid :disabled="!!editingId" />
          </div>
          <div class="flex flex-col gap-1">
            <label for="pos-salary" class="text-sm font-medium text-dark-muted">เงินเดือน</label>
            <InputNumber
              v-model="baseSalary"
              input-id="pos-salary"
              :min="0"
              :invalid="!!errors.base_salary"
              :aria-describedby="errors.base_salary ? 'pos-salary-error' : undefined"
              fluid
              :disabled="!!editingId"
            />
            <small v-if="errors.base_salary" id="pos-salary-error" class="text-red-400" role="alert">{{ errors.base_salary }}</small>
          </div>
        </div>

        <div v-if="!editingId" class="grid grid-cols-3 gap-3">
          <div class="flex flex-col gap-1">
            <label id="pos-occupancy" class="text-sm font-medium text-dark-muted">สถานะการครอง</label>
            <Select
              v-model="occupancy"
              label-id="pos-occupancy"
              :options="OCCUPANCY_OPTIONS"
              option-label="label"
              option-value="value"
              fluid
            />
          </div>
          <div class="flex flex-col gap-1">
            <label for="pos-months" class="text-sm font-medium text-dark-muted">เดือนที่นับ (1-12)</label>
            <InputNumber
              v-model="monthsCounted"
              input-id="pos-months"
              :min="1"
              :max="12"
              :invalid="!!errors.months_counted"
              :aria-describedby="errors.months_counted ? 'pos-months-error' : undefined"
              fluid
            />
            <small v-if="errors.months_counted" id="pos-months-error" class="text-red-400" role="alert">{{ errors.months_counted }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="pos-effective" class="text-sm font-medium text-dark-muted">วันเริ่มมีผล</label>
            <InputText
              id="pos-effective"
              v-model="effectiveFrom"
              type="date"
              :invalid="!!errors.effective_from"
              :aria-describedby="errors.effective_from ? 'pos-effective-error' : undefined"
              fluid
            />
            <small v-if="errors.effective_from" id="pos-effective-error" class="text-red-400" role="alert">{{ errors.effective_from }}</small>
          </div>
        </div>
        <p v-else class="text-xs text-dark-muted">
          แก้เฉพาะเลขถือจ่าย/ประเภท/คำสั่งตั้งอัตรา — การเปลี่ยนเงินเดือน/ระดับ/หน่วยงาน ให้เพิ่ม "เวอร์ชัน" ใหม่แทน
        </p>

        <div class="flex flex-col gap-1">
          <label for="pos-doc-no" class="text-sm font-medium text-dark-muted">เลขที่คำสั่งตั้งอัตรา</label>
          <InputText id="pos-doc-no" v-model="createdDocNo" fluid />
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
          <ListEmptyState message="ยังไม่มีเวอร์ชัน" />
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
          <div class="flex flex-col gap-1">
            <label for="version-effective-from" class="text-sm font-medium text-dark-muted">วันเริ่มมีผล</label>
            <InputText
              id="version-effective-from"
              v-model="vEffectiveFrom"
              type="date"
              :invalid="!!versionErrors.effective_from"
              fluid
            />
            <small v-if="versionErrors.effective_from" class="text-red-400" role="alert">{{ versionErrors.effective_from }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="version-base-salary" class="text-sm font-medium text-dark-muted">เงินเดือน</label>
            <InputNumber
              input-id="version-base-salary"
              v-model="vBaseSalary"
              :min="0"
              :invalid="!!versionErrors.base_salary"
              fluid
            />
            <small v-if="versionErrors.base_salary" class="text-red-400" role="alert">{{ versionErrors.base_salary }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="version-level-code" class="text-sm font-medium text-dark-muted">ระดับ</label>
            <InputText id="version-level-code" v-model="vLevelCode" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <label id="version-organization-label" class="text-sm font-medium text-dark-muted">หน่วยงาน</label>
            <Select
              v-model="vOrganizationId"
              :options="organizations ?? []"
              option-label="name_th"
              option-value="id"
              aria-labelledby="version-organization-label"
              :invalid="!!versionErrors.organization_id"
              filter
              fluid
            />
            <small v-if="versionErrors.organization_id" class="text-red-400" role="alert">{{ versionErrors.organization_id }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label id="version-occupancy-label" class="text-sm font-medium text-dark-muted">สถานะการครอง</label>
            <Select
              v-model="vOccupancy"
              :options="OCCUPANCY_OPTIONS"
              option-label="label"
              option-value="value"
              aria-labelledby="version-occupancy-label"
              fluid
            />
          </div>
          <div class="flex flex-col gap-1">
            <label for="version-months-counted" class="text-sm font-medium text-dark-muted">เดือนที่นับ</label>
            <InputNumber
              input-id="version-months-counted"
              v-model="vMonthsCounted"
              :min="1"
              :max="12"
              :invalid="!!versionErrors.months_counted"
              fluid
            />
            <small v-if="versionErrors.months_counted" class="text-red-400" role="alert">{{ versionErrors.months_counted }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label id="version-salary-basis-label" class="text-sm font-medium text-dark-muted">สถานะเงินเดือน</label>
            <Select
              v-model="vSalaryBasis"
              :options="[{ value: 'estimated', label: 'ประมาณการ' }, { value: 'actual', label: 'ยืนยันแล้ว' }]"
              option-label="label"
              option-value="value"
              aria-labelledby="version-salary-basis-label"
              fluid
            />
          </div>
          <div class="flex flex-col gap-1">
            <label id="version-approval-label" class="text-sm font-medium text-dark-muted">สถานะการอนุมัติ</label>
            <Select
              v-model="vApprovalStatus"
              :options="[{ value: 'approved', label: 'อนุมัติแล้ว' }, { value: 'requested', label: 'รออนุมัติ (ไม่นับงบ)' }]"
              option-label="label"
              option-value="value"
              aria-labelledby="version-approval-label"
              fluid
            />
          </div>
          <div class="flex flex-col gap-1">
            <label for="version-order-doc-no" class="text-sm font-medium text-dark-muted">เลขที่คำสั่ง</label>
            <InputText id="version-order-doc-no" v-model="vOrderDocNo" fluid />
          </div>
        </div>
        <div class="mt-3 flex justify-end">
          <Button
            label="เพิ่มเวอร์ชัน"
            icon="pi pi-plus"
            :loading="createVersionMutation.isPending.value"
            @click="onAddVersion"
          />
        </div>
      </div>
    </Dialog>

    <!-- Allowances dialog -->
    <Dialog v-model:visible="showAllowances" header="สิทธิ์เงินเพิ่มของอัตรา" modal class="w-full max-w-2xl">
      <DataTable :value="allowances ?? []" :loading="allowancesLoading" data-key="id">
        <template #empty>
          <ListEmptyState message="ยังไม่มีสิทธิ์ (ไม่มีแถว = ไม่มีสิทธิ์)" />
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
          <div class="flex flex-col gap-1">
            <label id="allowance-type-label" class="text-sm font-medium text-dark-muted">ชนิดเงินเพิ่ม</label>
            <Select
              v-model="aTypeId"
              :options="allowanceTypes ?? []"
              option-label="name_th"
              option-value="id"
              aria-labelledby="allowance-type-label"
              :invalid="!!allowanceErrors.allowance_type_id"
              filter
              fluid
            />
            <small v-if="allowanceErrors.allowance_type_id" class="text-red-400" role="alert">{{ allowanceErrors.allowance_type_id }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="allowance-effective-from" class="text-sm font-medium text-dark-muted">วันเริ่มมีสิทธิ์</label>
            <InputText
              id="allowance-effective-from"
              v-model="aEffectiveFrom"
              type="date"
              :invalid="!!allowanceErrors.effective_from"
              fluid
            />
            <small v-if="allowanceErrors.effective_from" class="text-red-400" role="alert">{{ allowanceErrors.effective_from }}</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="allowance-doc-no" class="text-sm font-medium text-dark-muted">เลขที่คำสั่ง</label>
            <InputText id="allowance-doc-no" v-model="aDocNo" fluid />
          </div>
        </div>
        <div class="mt-3 flex justify-end">
          <Button
            label="เพิ่มสิทธิ์"
            icon="pi pi-plus"
            :loading="createAllowanceMutation.isPending.value"
            @click="onAddAllowance"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>

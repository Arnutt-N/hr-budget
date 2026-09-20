<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { formatThaiDate } from '@/lib/date'
import type { AllowanceType } from '@/types/allowance'
import {
  useAllowanceTypeList,
  useUpdateAllowanceType,
  useAllowanceRates,
  useCreateAllowanceRate,
  useDeleteAllowanceRate,
} from '@/queries/useAllowances'

const toast = useToast()
const confirmDelete = useDeleteConfirm()

const { data: types, isLoading, isError, error } = useAllowanceTypeList()
const updateMutation = useUpdateAllowanceType()

const SCOPE_LABELS: Record<string, string> = { position: 'ผูกตำแหน่ง', personal: 'ผูกบุคคล' }
const BASIS_LABELS: Record<string, string> = {
  flat: 'อัตราคงที่',
  percent_of_salary: '% ของเงินเดือน',
  by_level: 'แยกตามระดับ',
  derived: 'อ้างอิงตัวอื่น',
}
const BUDGET_LABELS: Record<string, string> = {
  establishment: 'จากอัตรากำลัง',
  actuals: 'จากผู้รับจริง',
  manual: 'กรอกเอง',
}

// ---------- flags edit dialog ----------
const showEdit = ref(false)
const editing = ref<AllowanceType | null>(null)
const editForm = ref({
  vacant_eligible: false,
  report_scope_personnel: false,
  report_scope_operating: false,
  budget_basis: 'establishment' as string,
  legal_ref: '',
  is_active: true,
})

function openEdit(t: AllowanceType): void {
  editing.value = t
  const scopes = (t.report_scope ?? '').split(',')
  editForm.value = {
    vacant_eligible: !!t.vacant_eligible,
    report_scope_personnel: scopes.includes('personnel'),
    report_scope_operating: scopes.includes('operating'),
    budget_basis: t.budget_basis,
    legal_ref: t.legal_ref ?? '',
    is_active: !!t.is_active,
  }
  showEdit.value = true
}

async function onSaveFlags(): Promise<void> {
  if (!editing.value) return
  const reportScope: string[] = []
  if (editForm.value.report_scope_personnel) reportScope.push('personnel')
  if (editForm.value.report_scope_operating) reportScope.push('operating')
  try {
    await updateMutation.mutateAsync({
      id: editing.value.id,
      data: {
        vacant_eligible: editForm.value.vacant_eligible,
        report_scope: reportScope.length ? reportScope : ['personnel'],
        budget_basis: editForm.value.budget_basis as AllowanceType['budget_basis'],
        legal_ref: editForm.value.legal_ref,
        is_active: editForm.value.is_active,
      },
    })
    toast.add({ severity: 'success', summary: 'บันทึกการตั้งค่าสำเร็จ', life: 3000 })
    showEdit.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}

// ---------- rates dialog ----------
const showRates = ref(false)
const activeTypeId = ref<number | null>(null)
const activeType = computed(() => types.value?.find((t) => t.id === activeTypeId.value) ?? null)
const { data: rates, isLoading: ratesLoading } = useAllowanceRates(activeTypeId)
const createRateMutation = useCreateAllowanceRate()
const deleteRateMutation = useDeleteAllowanceRate()

const rateForm = ref({
  level_code: '',
  amount: null as number | null,
  percent: null as number | null,
  derives_from_type_id: null as number | null,
  fallback_amount: null as number | null,
  effective_from: '',
  doc_no: '',
})

const deriveOptions = computed(() =>
  (types.value ?? [])
    .filter((t) => t.id !== activeTypeId.value)
    .map((t) => ({ value: t.id, label: t.short_name ?? t.name_th })),
)

function openRates(t: AllowanceType): void {
  activeTypeId.value = t.id
  rateForm.value = {
    level_code: '',
    amount: null,
    percent: null,
    derives_from_type_id: null,
    fallback_amount: null,
    effective_from: '',
    doc_no: '',
  }
  showRates.value = true
}

async function onAddRate(): Promise<void> {
  if (!activeTypeId.value || !rateForm.value.effective_from) return
  try {
    await createRateMutation.mutateAsync({
      typeId: activeTypeId.value,
      data: {
        level_code: rateForm.value.level_code || null,
        amount: rateForm.value.amount,
        percent: rateForm.value.percent,
        derives_from_type_id: rateForm.value.derives_from_type_id,
        fallback_amount: rateForm.value.fallback_amount,
        effective_from: rateForm.value.effective_from,
        doc_no: rateForm.value.doc_no || null,
      },
    })
    toast.add({ severity: 'success', summary: 'บันทึกอัตราสำเร็จ', life: 3000 })
    rateForm.value = { ...rateForm.value, amount: null, percent: null, derives_from_type_id: null, fallback_amount: null, doc_no: '' }
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกอัตราไม่สำเร็จ', detail: message, life: 5000 })
  }
}

function onDeleteRate(rateId: number): void {
  const rate = rates.value?.find((r) => r.id === rateId)
  const rateLabel = rate?.level_code ?? (rate?.amount != null ? `${rate.amount} บาท` : `#${rateId}`)
  confirmDelete({
    message: `ยืนยันลบอัตรา "${rateLabel}"?`,
    accept: async () => {
      if (!activeTypeId.value) return
      try {
        await deleteRateMutation.mutateAsync({ typeId: activeTypeId.value, rateId })
        toast.add({ severity: 'success', summary: 'ลบอัตราสำเร็จ', life: 3000 })
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
    <PageHeader title="แคตตาล็อกเงินเพิ่ม" />

    <QueryErrorState v-if="isError" :error="error" />

    <div class="table-scroll" v-else>
    <DataTable
      :value="types ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลเงินเพิ่ม" />
      </template>

      <Column field="short_name" header="ชื่อย่อ">
        <template #body="{ data }">{{ data.short_name ?? data.code }}</template>
      </Column>
      <Column field="name_th" header="ชื่อเต็ม" />
      <Column header="รายการงบ (แกนบัญชี)">
        <template #body="{ data }">{{ data.expense_item_name ?? '—' }}</template>
      </Column>
      <Column header="ขอบเขตสิทธิ์">
        <template #body="{ data }">{{ SCOPE_LABELS[data.scope] ?? data.scope }}</template>
      </Column>
      <Column header="อัตราว่างนับ">
        <template #body="{ data }">
          <Tag :value="data.vacant_eligible ? 'นับ' : 'ไม่นับ'" :severity="data.vacant_eligible ? 'success' : 'secondary'" />
        </template>
      </Column>
      <Column header="วิธีคำนวณ">
        <template #body="{ data }">{{ BASIS_LABELS[data.basis] ?? data.basis }}</template>
      </Column>
      <Column header="ตั้งงบจาก">
        <template #body="{ data }">{{ BUDGET_LABELS[data.budget_basis] ?? data.budget_basis }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button label="อัตรา" size="small" text severity="info" @click="openRates(data)" />
            <Button label="ตั้งค่า" size="small" text @click="openEdit(data)" />
          </div>
        </template>
      </Column>
    </DataTable>
    </div>

    <!-- Flags dialog -->
    <Dialog v-model:visible="showEdit" :header="`ตั้งค่า: ${editing?.name_th ?? ''}`" modal class="w-full max-w-md">
      <div class="space-y-4">
        <label class="flex items-center gap-2 text-sm">
          <Checkbox v-model="editForm.vacant_eligible" binary />
          อัตราว่างนับเงินเพิ่มนี้ (นโยบาย – ไม่เกี่ยวกับขอบเขตสิทธิ์)
        </label>

        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">รายงานรวมกับ (แกนบริหาร)</span>
          <label class="flex items-center gap-2 text-sm">
            <Checkbox v-model="editForm.report_scope_personnel" binary /> ภาพรวมค่าใช้จ่ายบุคลากร
          </label>
          <label class="flex items-center gap-2 text-sm">
            <Checkbox v-model="editForm.report_scope_operating" binary /> งบดำเนินงาน
          </label>
        </div>

        <FormField id="at-basis" labelled-by label="ตั้งงบจาก">
          <Select
            aria-labelledby="at-basis-label"
            v-model="editForm.budget_basis"
            :options="[
              { value: 'establishment', label: 'จากอัตรากำลัง' },
              { value: 'actuals', label: 'จากผู้รับจริง (snapshot)' },
              { value: 'manual', label: 'กรอกเองเป็นก้อน' },
            ]"
            option-label="label"
            option-value="value"
            fluid
          />
        </FormField>

        <FormField id="at-legal" label="ระเบียบ/ประกาศอ้างอิง">
          <InputText id="at-legal" v-model="editForm.legal_ref" fluid />
        </FormField>

        <label class="flex items-center gap-2 text-sm">
          <Checkbox v-model="editForm.is_active" binary /> ใช้งานอยู่
        </label>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text @click="showEdit = false" />
          <Button label="บันทึก" :loading="updateMutation.isPending.value" @click="onSaveFlags" />
        </div>
      </div>
    </Dialog>

    <!-- Rates dialog -->
    <Dialog v-model:visible="showRates" :header="`อัตราของ: ${activeType?.name_th ?? ''}`" modal class="w-full max-w-4xl">
      <div class="table-scroll">
      <DataTable :value="rates ?? []" :loading="ratesLoading" data-key="id">
        <template #empty>
          <p class="py-3 text-center text-dark-muted">ยังไม่มีอัตรา (ไม่มีแถว = ไม่มีสิทธิ์)</p>
        </template>
        <Column field="level_code" header="ระดับ">
          <template #body="{ data }">{{ data.level_code ?? 'ทุกระดับ' }}</template>
        </Column>
        <Column header="จำนวน">
          <template #body="{ data }">
            <span v-if="data.derives_from_type_id">
              อ้างอิง: {{ data.derives_from_short_name ?? data.derives_from_type_id }}
              <span v-if="data.fallback_amount !== null"> (พื้น {{ Number(data.fallback_amount).toLocaleString('th-TH') }})</span>
            </span>
            <span v-else-if="data.amount !== null">{{ Number(data.amount).toLocaleString('th-TH') }} บาท</span>
            <span v-else-if="data.percent !== null">{{ data.percent }}%</span>
            <span v-else>—</span>
          </template>
        </Column>
        <Column header="ช่วงมีผล">
          <template #body="{ data }">
            {{ formatThaiDate(data.effective_from) }} – {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
          </template>
        </Column>
        <Column field="doc_no" header="เอกสาร">
          <template #body="{ data }">{{ data.doc_no ?? '—' }}</template>
        </Column>
        <Column header="" class="text-right">
          <template #body="{ data }">
            <Button label="ลบ" size="small" text severity="danger" @click="onDeleteRate(data.id)" />
          </template>
        </Column>
      </DataTable>
      </div>

      <div class="mt-4 rounded-lg border border-dark-border p-4">
        <h3 class="mb-3 font-semibold text-white">เพิ่มอัตราใหม่</h3>
        <div class="grid grid-cols-3 gap-3">
          <FormField id="at-level" label="ระดับ (เว้นว่าง = ทุกระดับ)">
          <InputText id="at-level" v-model="rateForm.level_code" placeholder="ระดับ (เว้นว่าง = ทุกระดับ)" />
          </FormField>
          <FormField id="at-amount" label="จำนวนเงิน (บาท)">
          <InputNumber input-id="at-amount" v-model="rateForm.amount" :min="0" placeholder="จำนวนเงิน (บาท)" fluid />
          </FormField>
          <FormField id="at-percent" label="หรือ %">
          <InputNumber input-id="at-percent" v-model="rateForm.percent" :min="0" :max="100" placeholder="หรือ %" fluid />
          </FormField>
          <FormField id="at-derive" labelled-by label="หรือ อ้างอิงเงินเพิ่มตัวอื่น">
          <Select
            aria-labelledby="at-derive-label"
            v-model="rateForm.derives_from_type_id"
            :options="deriveOptions"
            option-label="label"
            option-value="value"
            placeholder="หรือ อ้างอิงเงินเพิ่มตัวอื่น"
            show-clear
            fluid
          />
          </FormField>
          <FormField id="at-fallback" label="ยอดพื้น (เฉพาะอ้างอิง)">
          <InputNumber input-id="at-fallback" v-model="rateForm.fallback_amount" :min="0" placeholder="ยอดพื้น (เฉพาะอ้างอิง)" fluid />
          </FormField>
          <FormField id="at-from" label="วันเริ่มมีผล">
          <InputText id="at-from" v-model="rateForm.effective_from" type="date" placeholder="วันเริ่มมีผล" />
          </FormField>
          <FormField id="at-doc" label="เลขที่เอกสาร">
          <InputText id="at-doc" v-model="rateForm.doc_no" placeholder="เลขที่เอกสาร" />
          </FormField>
        </div>
        <p class="mt-2 text-xs text-dark-muted">
          กติกา: ใส่ได้ทีละอย่าง (จำนวนเงิน หรือ % หรือ อ้างอิง) · การอ้างอิงที่ก่อให้เกิดวงจรจะถูกปฏิเสธโดยระบบ
        </p>
        <div class="mt-3 flex justify-end">
          <Button
            label="เพิ่มอัตรา"
            icon="pi pi-plus"
            :loading="createRateMutation.isPending.value"
            :disabled="!rateForm.effective_from"
            :title="!rateForm.effective_from ? 'เลือกวันเริ่มมีผลก่อน' : undefined"
            @click="onAddRate"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>

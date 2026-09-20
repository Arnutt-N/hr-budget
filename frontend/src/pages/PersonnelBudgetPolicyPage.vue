<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { formatThaiDate } from '@/lib/date'
import { vacancyTypeLabel, VACANCY_TYPE_OPTIONS, CALC_MODE_OPTIONS } from '@/lib/personnel'
import {
  usePersonnelBudgetPolicies,
  useCreatePersonnelBudgetPolicy,
  useUpdatePersonnelBudgetPolicy,
} from '@/queries/usePersonnel'
import { useFiscalYearList } from '@/queries/useFiscalYears'

const toast = useToast()

const { data: policies, isLoading, isError, error } = usePersonnelBudgetPolicies()
const createMutation = useCreatePersonnelBudgetPolicy()
const updateMutation = useUpdatePersonnelBudgetPolicy()
const { data: fiscalYears } = useFiscalYearList()

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขนโยบาย' : 'สร้างนโยบายปีงบใหม่'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const form = ref({
  fiscal_year_id: 0,
  vacancy_rule: 'ready_to_fill' as string,
  calc_mode: 'prorate' as string,
  buffer_percent: null as number | null,
  reference_date: '',
})

function openCreate(): void {
  editingId.value = null
  form.value = { fiscal_year_id: 0, vacancy_rule: 'ready_to_fill', calc_mode: 'prorate', buffer_percent: null, reference_date: '' }
  showDialog.value = true
}

function openEdit(p: { id: number; vacancy_rule: string | null; calc_mode: string; buffer_percent: number | null; reference_date: string | null }): void {
  editingId.value = p.id
  form.value = {
    fiscal_year_id: 0,
    vacancy_rule: p.vacancy_rule ?? 'ready_to_fill',
    calc_mode: p.calc_mode,
    buffer_percent: p.buffer_percent,
    reference_date: p.reference_date ?? '',
  }
  showDialog.value = true
}

async function onSave(): Promise<void> {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({
        id: editingId.value,
        data: {
          vacancy_rule: form.value.vacancy_rule,
          calc_mode: form.value.calc_mode,
          buffer_percent: form.value.buffer_percent,
          reference_date: form.value.reference_date || null,
        },
      })
      toast.add({ severity: 'success', summary: 'แก้ไขนโยบายสำเร็จ', life: 3000 })
    } else {
      if (!form.value.fiscal_year_id) return
      await createMutation.mutateAsync({
        fiscal_year_id: form.value.fiscal_year_id,
        vacancy_rule: form.value.vacancy_rule as 'transfer_request' | 'eligibility_list' | 'ready_to_fill',
        calc_mode: form.value.calc_mode as 'prorate',
        buffer_percent: form.value.buffer_percent,
        reference_date: form.value.reference_date || null,
      })
      toast.add({ severity: 'success', summary: 'สร้างนโยบายสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}
</script>

<template>
  <div>
    <PageHeader
      title="นโยบายการคำนวณงบบุคลากร"
      subtitle="เกณฑ์รายปีงบ – หนึ่งแถวต่อปี (ตัวคำนวณใช้แถวนี้ตัดสินวิธีคิด)"
    >
      <Button label="สร้างนโยบาย" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <div class="table-scroll" v-else>
    <DataTable
      :value="policies ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีนโยบาย" />
      </template>
      <Column header="ปีงบ" field="fiscal_year" />
      <Column header="เกณฑ์อัตราว่าง">
        <template #body="{ data }">
          <Tag :value="vacancyTypeLabel(data.vacancy_rule)" />
        </template>
      </Column>
      <Column header="วิธีคิด">
        <template #body="{ data }">
          {{ data.calc_mode === 'prorate' ? 'แบ่งตามเดือนจริง' : 'นับเต็ม 12 เดือน' }}
        </template>
      </Column>
      <Column header="ช่องปรับ %">
        <template #body="{ data }">{{ data.buffer_percent ?? '—' }}</template>
      </Column>
      <Column header="วันอ้างอิง">
        <template #body="{ data }">{{ data.reference_date ? formatThaiDate(data.reference_date) : '—' }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
        </template>
      </Column>
    </DataTable>
    </div>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <div class="space-y-4">
        <FormField id="pbp-year" labelled-by label="ปีงบ">
          <Select
            aria-labelledby="pbp-year-label"
            v-model="form.fiscal_year_id"
            :options="fiscalYears ?? []"
            option-label="year"
            option-value="id"
            placeholder="เลือกปี"
            fluid
          />
        </FormField>
        <FormField id="pbp-rule" labelled-by label="เกณฑ์อัตราว่างที่นับเข้างบ">
          <Select
            aria-labelledby="pbp-rule-label"
            v-model="form.vacancy_rule"
            :options="VACANCY_TYPE_OPTIONS"
            option-label="label"
            option-value="value"
            fluid
          />
        </FormField>
        <FormField id="pbp-mode" labelled-by label="วิธีคิด">
          <Select
            aria-labelledby="pbp-mode-label"
            v-model="form.calc_mode"
            :options="CALC_MODE_OPTIONS"
            option-label="label"
            option-value="value"
            fluid
          />
        </FormField>
        <div class="grid grid-cols-2 gap-3">
          <FormField id="pbp-buffer" label="ช่องปรับ %">
            <InputNumber input-id="pbp-buffer" v-model="form.buffer_percent" :min="0" :max="100" fluid />
          </FormField>
          <FormField id="pbp-refdate" label="วันอ้างอิง">
            <InputText id="pbp-refdate" v-model="form.reference_date" type="date" fluid />
          </FormField>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button label="บันทึก" :loading="saving" :disabled="!editingId && !form.fiscal_year_id" @click="onSave" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

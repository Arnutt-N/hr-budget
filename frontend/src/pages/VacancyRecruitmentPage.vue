<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import { formatThaiDate } from '@/lib/date'
import { vacancyTypeLabel, VACANCY_TYPE_OPTIONS } from '@/lib/personnel'
import {
  useVacancyRecruitmentList,
  useCreateVacancyRecruitment,
  useDeleteVacancyRecruitment,
} from '@/queries/usePersonnel'
import { usePositionList } from '@/queries/usePositions'
import { useFiscalYearList } from '@/queries/useFiscalYears'

const toast = useToast()
const confirm = useConfirm()

const { data: items, isLoading, isError, error } = useVacancyRecruitmentList()
const createMutation = useCreateVacancyRecruitment()
const deleteMutation = useDeleteVacancyRecruitment()
const { data: positions } = usePositionList(ref({}))
const { data: fiscalYears } = useFiscalYearList()

const showDialog = ref(false)
const form = ref({
  position_id: 0,
  fiscal_year_id: 0,
  type: 'ready_to_fill' as string,
  doc_no: '',
  doc_date: '',
})
const saving = computed(() => createMutation.isPending.value)

function openCreate(): void {
  form.value = { position_id: 0, fiscal_year_id: 0, type: 'ready_to_fill', doc_no: '', doc_date: '' }
  showDialog.value = true
}

async function onSave(): Promise<void> {
  if (!form.value.position_id || !form.value.fiscal_year_id) return
  try {
    await createMutation.mutateAsync({
      position_id: form.value.position_id,
      fiscal_year_id: form.value.fiscal_year_id,
      type: form.value.type as 'ready_to_fill',
      doc_no: form.value.doc_no || null,
      doc_date: form.value.doc_date || null,
    })
    toast.add({ severity: 'success', summary: 'บันทึกหลักฐานสรรหาสำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}

function confirmDelete(item: { id: number; pay_no: string | null }): void {
  confirm.require({
    message: `ลบหลักฐานของอัตรา ${item.pay_no ?? item.id}?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(item.id)
        toast.add({ severity: 'success', summary: 'ลบหลักฐานสำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">อัตราว่างพร้อมบรรจุ (หลักฐานสรรหา)</h1>
      <Button label="เพิ่มหลักฐาน" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="items ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีหลักฐานสรรหา</p>
      </template>
      <Column field="pay_no" header="เลขถือจ่าย" />
      <Column field="pos_no" header="เลขที่ตำแหน่ง">
        <template #body="{ data }">{{ data.pos_no ?? '—' }}</template>
      </Column>
      <Column field="organization_name" header="หน่วยงาน" />
      <Column header="ปีงบ">
        <template #body="{ data }">
          {{ fiscalYears?.find((fy) => fy.id === data.fiscal_year_id)?.year ?? data.fiscal_year_id }}
        </template>
      </Column>
      <Column header="ประเภท">
        <template #body="{ data }">
          <Tag :value="vacancyTypeLabel(data.type)" />
        </template>
      </Column>
      <Column header="เอกสาร">
        <template #body="{ data }">
          <span>{{ data.doc_no ?? '—' }}</span>
          <span v-if="data.doc_date" class="ml-2 text-dark-muted">{{ formatThaiDate(data.doc_date) }}</span>
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" header="เพิ่มหลักฐานสรรหา" modal class="w-full max-w-md">
      <div class="space-y-4">
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">อัตรากำลัง</span>
          <Select
            v-model="form.position_id"
            :options="positions ?? []"
            option-label="pay_no"
            option-value="id"
            placeholder="เลือกอัตรา (เลขถือจ่าย)"
            filter
            fluid
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">ปีงบ</span>
            <Select
              v-model="form.fiscal_year_id"
              :options="fiscalYears ?? []"
              option-label="year"
              option-value="id"
              placeholder="เลือกปี"
              fluid
            />
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">ประเภทหลักฐาน</span>
            <Select
              v-model="form.type"
              :options="VACANCY_TYPE_OPTIONS"
              option-label="label"
              option-value="value"
              fluid
            />
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">เลขที่เอกสาร</span>
          <InputText v-model="form.doc_no" fluid />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">วันที่เอกสาร</span>
          <InputText v-model="form.doc_date" type="date" fluid />
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button label="บันทึก" :loading="saving" :disabled="!form.position_id || !form.fiscal_year_id" @click="onSave" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

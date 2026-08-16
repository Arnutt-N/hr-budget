<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
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
import {
  usePersonnelAllowanceList,
  useCreatePersonnelAllowance,
  useDeletePersonnelAllowance,
} from '@/queries/usePersonnel'
import { usePositionList } from '@/queries/usePositions'
import { useAllowanceTypeList } from '@/queries/useAllowances'

const toast = useToast()
const confirm = useConfirm()

const { data: items, isLoading, isError, error } = usePersonnelAllowanceList()
const createMutation = useCreatePersonnelAllowance()
const deleteMutation = useDeletePersonnelAllowance()
const { data: positions } = usePositionList(ref({}))
const { data: types } = useAllowanceTypeList()

const showDialog = ref(false)
const saving = computed(() => createMutation.isPending.value)
const form = ref({
  person_id: '',
  position_id: 0,
  allowance_type_id: 0,
  amount: 0,
  effective_from: '',
  doc_no: '',
})

function openCreate(): void {
  form.value = { person_id: '', position_id: 0, allowance_type_id: 0, amount: 0, effective_from: '', doc_no: '' }
  showDialog.value = true
}

async function onSave(): Promise<void> {
  if (!form.value.person_id || !form.value.position_id || !form.value.allowance_type_id || !form.value.effective_from) return
  try {
    await createMutation.mutateAsync({
      person_id: form.value.person_id,
      position_id: form.value.position_id,
      allowance_type_id: form.value.allowance_type_id,
      amount: form.value.amount,
      effective_from: form.value.effective_from,
      doc_no: form.value.doc_no || null,
    })
    toast.add({ severity: 'success', summary: 'บันทึกการรับจริงสำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}

function confirmDelete(id: number): void {
  confirm.require({
    message: 'ลบรายการรับจริงนี้?',
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(id)
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
      <div>
        <h1 class="text-2xl font-bold text-white">การรับจริงเงินเพิ่ม (รายคน)</h1>
        <p class="mt-1 text-sm text-dark-muted">ใช้ตอนเบิกจ่ายจริง — ไม่ใช่อัตรากำลัง (สิทธิ์อยู่ที่หน้าอัตรา)</p>
      </div>
      <Button label="เพิ่มการรับจริง" icon="pi pi-plus" @click="openCreate" />
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
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลการรับจริง</p>
      </template>
      <Column field="person_id" header="รหัสบุคคล" />
      <Column field="pay_no" header="เลขถือจ่าย" />
      <Column field="allowance_name" header="เงินเพิ่ม">
        <template #body="{ data }">{{ data.short_name ?? data.allowance_name ?? '—' }}</template>
      </Column>
      <Column field="amount" header="ยอด/เดือน">
        <template #body="{ data }">{{ Number(data.amount).toLocaleString('th-TH') }}</template>
      </Column>
      <Column header="ช่วงรับ">
        <template #body="{ data }">
          {{ formatThaiDate(data.effective_from) }} — {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
        </template>
      </Column>
      <Column field="doc_no" header="เอกสาร">
        <template #body="{ data }">{{ data.doc_no ?? '—' }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data.id)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" header="เพิ่มการรับจริง" modal class="w-full max-w-md">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">รหัสบุคคล</span>
            <InputText v-model="form.person_id" placeholder="เช่น P-1001" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">ยอด/เดือน</span>
            <InputNumber v-model="form.amount" :min="0" fluid />
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">อัตรากำลัง</span>
          <Select
            v-model="form.position_id"
            :options="positions ?? []"
            option-label="pay_no"
            option-value="id"
            placeholder="เลือกอัตรา"
            filter
            fluid
          />
        </div>
        <div class="flex flex-col gap-1">
          <span class="text-sm font-medium text-dark-muted">ชนิดเงินเพิ่ม</span>
          <Select
            v-model="form.allowance_type_id"
            :options="types ?? []"
            option-label="name_th"
            option-value="id"
            placeholder="เลือกชนิด"
            filter
            fluid
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">วันเริ่มรับ</span>
            <InputText v-model="form.effective_from" type="date" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <span class="text-sm font-medium text-dark-muted">เลขที่คำสั่ง</span>
            <InputText v-model="form.doc_no" fluid />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button label="บันทึก" :loading="saving" @click="onSave" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

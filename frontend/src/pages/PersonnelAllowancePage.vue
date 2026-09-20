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
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { formatThaiDate } from '@/lib/date'
import {
  usePersonnelAllowanceList,
  useCreatePersonnelAllowance,
  useDeletePersonnelAllowance,
} from '@/queries/usePersonnel'
import { usePositionList } from '@/queries/usePositions'
import { useAllowanceTypeList } from '@/queries/useAllowances'

const toast = useToast()
const confirmDeletePrompt = useDeleteConfirm()

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
  const missing: string[] = []
  if (!form.value.person_id) missing.push('รหัสบุคคล')
  if (!form.value.position_id) missing.push('อัตรากำลัง')
  if (!form.value.allowance_type_id) missing.push('ชนิดเงินเพิ่ม')
  if (!form.value.effective_from) missing.push('วันเริ่มรับ')
  if (missing.length > 0) {
    toast.add({ severity: 'error', summary: 'กรุณากรอกข้อมูลให้ครบ', detail: `ยังขาด: ${missing.join('、 ')}`, life: 5000 })
    return
  }
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
  const target = items.value?.find((i) => i.id === id)
  const who = target ? `${target.person_id} จำนวน ${target.amount}` : `#${id}`
  confirmDeletePrompt({
    message: `ลบรายการรับจริงของ "${who}"?`,
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
    <PageHeader
      title="การรับจริงเงินเพิ่ม (รายคน)"
      subtitle="ใช้ตอนเบิกจ่ายจริง – ไม่ใช่อัตรากำลัง (สิทธิ์อยู่ที่หน้าอัตรา)"
    >
      <Button label="เพิ่มการรับจริง" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <div class="table-scroll" v-else>
    <DataTable
      :value="items ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีข้อมูลการรับจริง" />
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
          {{ formatThaiDate(data.effective_from) }} – {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
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
    </div>

    <Dialog v-model:visible="showDialog" header="เพิ่มการรับจริง" modal class="w-full max-w-md">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <FormField id="pa-person" label="รหัสบุคคล">
            <InputText id="pa-person" v-model="form.person_id" placeholder="เช่น P-1001" fluid />
          </FormField>
          <FormField id="pa-amount" label="ยอด/เดือน">
            <InputNumber input-id="pa-amount" v-model="form.amount" :min="0" fluid />
          </FormField>
        </div>
        <FormField id="pa-position" labelled-by label="อัตรากำลัง">
          <Select
            aria-labelledby="pa-position-label"
            v-model="form.position_id"
            :options="positions ?? []"
            option-label="pay_no"
            option-value="id"
            placeholder="เลือกอัตรา"
            filter
            fluid
          />
        </FormField>
        <FormField id="pa-type" labelled-by label="ชนิดเงินเพิ่ม">
          <Select
            aria-labelledby="pa-type-label"
            v-model="form.allowance_type_id"
            :options="types ?? []"
            option-label="name_th"
            option-value="id"
            placeholder="เลือกชนิด"
            filter
            fluid
          />
        </FormField>
        <div class="grid grid-cols-2 gap-3">
          <FormField id="pa-from" label="วันเริ่มรับ">
            <InputText id="pa-from" v-model="form.effective_from" type="date" fluid />
          </FormField>
          <FormField id="pa-doc" label="เลขที่คำสั่ง">
            <InputText id="pa-doc" v-model="form.doc_no" fluid />
          </FormField>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button label="บันทึก" :loading="saving" @click="onSave" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

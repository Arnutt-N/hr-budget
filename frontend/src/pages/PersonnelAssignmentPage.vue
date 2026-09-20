<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import { formatThaiDate } from '@/lib/date'
import {
  usePersonnelAssignmentList,
  useCreatePersonnelAssignment,
  useDeletePersonnelAssignment,
} from '@/queries/usePersonnel'
import { usePositionList } from '@/queries/usePositions'
import { useOrganizationList } from '@/queries/useOrganizations'

const toast = useToast()
const confirmDeletePrompt = useDeleteConfirm()

const { data: items, isLoading, isError, error } = usePersonnelAssignmentList()
const createMutation = useCreatePersonnelAssignment()
const deleteMutation = useDeletePersonnelAssignment()
const { data: positions } = usePositionList(ref({}))
const { data: organizations } = useOrganizationList()

const showDialog = ref(false)
const saving = computed(() => createMutation.isPending.value)
const form = ref({
  person_id: '',
  position_id: 0,
  serving_organization_id: 0,
  effective_from: '',
  doc_no: '',
})

function openCreate(): void {
  form.value = { person_id: '', position_id: 0, serving_organization_id: 0, effective_from: '', doc_no: '' }
  showDialog.value = true
}

async function onSave(): Promise<void> {
  const missing: string[] = []
  if (!form.value.person_id) missing.push('รหัสบุคคล')
  if (!form.value.position_id) missing.push('อัตรากำลัง')
  if (!form.value.serving_organization_id) missing.push('หน่วยที่ไปช่วย')
  if (!form.value.effective_from) missing.push('วันเริ่ม')
  if (missing.length > 0) {
    toast.add({ severity: 'error', summary: 'กรุณากรอกข้อมูลให้ครบ', detail: `ยังขาด: ${missing.join('、 ')}`, life: 5000 })
    return
  }
  try {
    await createMutation.mutateAsync({
      person_id: form.value.person_id,
      position_id: form.value.position_id,
      serving_organization_id: form.value.serving_organization_id,
      effective_from: form.value.effective_from,
      doc_no: form.value.doc_no || null,
    })
    toast.add({ severity: 'success', summary: 'บันทึกการไปช่วยราชการสำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}

function confirmDelete(id: number): void {
  const target = items.value?.find((i) => i.id === id)
  const who = target ? `${target.person_id} (${target.serving_organization_name ?? target.serving_organization_id})` : `#${id}`
  confirmDeletePrompt({
    message: `ลบรายการไปช่วยราชการของ "${who}"?`,
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
      title="ไปช่วยราชการ"
      subtitle="งบยังอยู่ต้นสังกัดเสมอ – ตารางนี้ใช้รายงานเท่านั้น"
    >
      <Button label="เพิ่มการไปช่วย" icon="pi pi-plus" @click="openCreate" />
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
        <ListEmptyState message="ยังไม่มีข้อมูลการไปช่วยราชการ" />
      </template>
      <Column field="person_id" header="รหัสบุคคล" />
      <Column field="pay_no" header="เลขถือจ่าย" />
      <Column header="หน่วยที่ไปช่วย">
        <template #body="{ data }">
          <Tag :value="data.serving_organization_name ?? '—'" severity="info" />
        </template>
      </Column>
      <Column header="ช่วงเวลา">
        <template #body="{ data }">
          {{ formatThaiDate(data.effective_from) }} – {{ data.effective_to ? formatThaiDate(data.effective_to) : 'ปัจจุบัน' }}
        </template>
      </Column>
      <Column field="doc_no" header="คำสั่ง">
        <template #body="{ data }">{{ data.doc_no ?? '—' }}</template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data.id)" />
        </template>
      </Column>
    </DataTable>
    </div>

    <Dialog v-model:visible="showDialog" header="เพิ่มการไปช่วยราชการ" modal class="w-full max-w-md">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <FormField id="asgn-person" label="รหัสบุคคล">
            <InputText id="asgn-person" v-model="form.person_id" placeholder="เช่น P-1001" fluid />
          </FormField>
          <FormField id="asgn-from" label="วันเริ่ม">
            <InputText id="asgn-from" v-model="form.effective_from" type="date" fluid />
          </FormField>
        </div>
        <FormField id="asgn-position" labelled-by label="อัตรากำลัง (ต้นสังกัด)">
          <Select
            aria-labelledby="asgn-position-label"
            v-model="form.position_id"
            :options="positions ?? []"
            option-label="pay_no"
            option-value="id"
            placeholder="เลือกอัตรา"
            filter
            fluid
          />
        </FormField>
        <FormField id="asgn-org" labelled-by label="หน่วยที่ไปช่วย">
          <Select
            aria-labelledby="asgn-org-label"
            v-model="form.serving_organization_id"
            :options="organizations ?? []"
            option-label="name_th"
            option-value="id"
            placeholder="เลือกหน่วยงาน"
            filter
            fluid
          />
        </FormField>
        <FormField id="asgn-doc" label="เลขที่คำสั่ง">
          <InputText id="asgn-doc" v-model="form.doc_no" fluid />
        </FormField>
        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button label="บันทึก" :loading="saving" @click="onSave" />
        </div>
      </div>
    </Dialog>
  </div>
</template>

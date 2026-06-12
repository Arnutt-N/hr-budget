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
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { Organization } from '@/types/organization'
import {
  useOrganizationList,
  useCreateOrganization,
  useUpdateOrganization,
  useDeleteOrganization,
} from '@/queries/useOrganizations'

const confirm = useConfirm()
const toast = useToast()

const { data: organizations, isLoading, isError, error } = useOrganizationList()
const createMutation = useCreateOrganization()
const updateMutation = useUpdateOrganization()
const deleteMutation = useDeleteOrganization()

// Same labels as legacy Organization::getTypeLabels()
const ORG_TYPE_LABELS: Record<string, string> = {
  ministry: 'กระทรวง',
  department: 'กรม',
  division: 'กอง/สำนัก',
  section: 'กลุ่มงาน',
  province: 'จังหวัด',
  office: 'ส่วนราชการ',
}
const orgTypeOptions = Object.entries(ORG_TYPE_LABELS).map(([value, label]) => ({ value, label }))

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขหน่วยงาน' : 'เพิ่มหน่วยงาน'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const schema = toTypedSchema(
  z.object({
    code: z.string({ required_error: 'กรุณาระบุรหัสหน่วยงาน' }).min(1, 'กรุณาระบุรหัสหน่วยงาน').max(50, 'รหัสต้องไม่เกิน 50 ตัวอักษร'),
    name_th: z.string({ required_error: 'กรุณาระบุชื่อหน่วยงาน' }).min(1, 'กรุณาระบุชื่อหน่วยงาน').max(255, 'ชื่อต้องไม่เกิน 255 ตัวอักษร'),
    abbreviation: z.string().optional(),
    org_type: z.string().optional(),
    is_active: z.boolean().optional(),
  }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [code] = defineField('code')
const [nameTh] = defineField('name_th')
const [abbreviation] = defineField('abbreviation')
const [orgType] = defineField('org_type')
const [isActive] = defineField('is_active')

function openCreate(): void {
  editingId.value = null
  resetForm({ values: { code: '', name_th: '', abbreviation: '', org_type: undefined, is_active: true } })
  showDialog.value = true
}

function openEdit(org: Organization): void {
  editingId.value = org.id
  resetForm({
    values: {
      code: org.code,
      name_th: org.name_th,
      abbreviation: org.abbreviation ?? '',
      org_type: org.org_type ?? undefined,
      is_active: !!org.is_active,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      await updateMutation.mutateAsync({ id: editingId.value, data: values })
      toast.add({ severity: 'success', summary: 'แก้ไขหน่วยงานสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync(values)
      toast.add({ severity: 'success', summary: 'เพิ่มหน่วยงานสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(org: Organization): void {
  confirm.require({
    message: `ยืนยันลบหน่วยงาน "${org.name_th}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(org.id)
        toast.add({ severity: 'success', summary: 'ลบหน่วยงานสำเร็จ', life: 3000 })
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
      <h1 class="text-2xl font-bold text-white">หน่วยงาน</h1>
      <Button label="เพิ่มหน่วยงาน" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="organizations ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลหน่วยงาน</p>
      </template>

      <Column field="code" header="รหัส" sortable>
        <template #body="{ data }">
          <span class="font-mono text-sm">{{ data.code }}</span>
        </template>
      </Column>
      <Column field="name_th" header="ชื่อหน่วยงาน" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name_th }}</span>
          <span v-if="data.abbreviation" class="ml-1 text-sm text-dark-muted">({{ data.abbreviation }})</span>
        </template>
      </Column>
      <Column field="org_type" header="ประเภท" sortable>
        <template #body="{ data }">
          {{ data.org_type ? (ORG_TYPE_LABELS[data.org_type] ?? data.org_type) : '-' }}
        </template>
      </Column>
      <Column header="สถานะ">
        <template #body="{ data }">
          <Tag :value="data.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน'" :severity="data.is_active ? 'success' : 'secondary'" />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button label="แก้ไข" size="small" text @click="openEdit(data)" />
            <Button label="ลบ" size="small" text severity="danger" @click="confirmDelete(data)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <div class="flex flex-col gap-1">
          <label for="org-code" class="text-sm font-medium text-dark-muted">รหัสหน่วยงาน</label>
          <InputText id="org-code" v-model.trim="code" maxlength="50" :invalid="!!errors.code" fluid />
          <small v-if="errors.code" class="text-red-600" role="alert">{{ errors.code }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="org-name" class="text-sm font-medium text-dark-muted">ชื่อหน่วยงาน</label>
          <InputText id="org-name" v-model.trim="nameTh" :invalid="!!errors.name_th" fluid />
          <small v-if="errors.name_th" class="text-red-600" role="alert">{{ errors.name_th }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="org-abbr" class="text-sm font-medium text-dark-muted">ชื่อย่อ</label>
          <InputText id="org-abbr" v-model.trim="abbreviation" fluid />
        </div>

        <div class="flex flex-col gap-1">
          <label for="org-type" class="text-sm font-medium text-dark-muted">ประเภท</label>
          <Select
            v-model="orgType"
            label-id="org-type"
            :options="orgTypeOptions"
            option-label="label"
            option-value="value"
            placeholder="-- เลือก --"
            show-clear
            fluid
          />
        </div>

        <label v-if="editingId" class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isActive" binary input-id="org-is-active" />
          ใช้งาน
        </label>

        <div class="flex justify-end gap-2 pt-2">
          <Button label="ยกเลิก" severity="secondary" text :disabled="saving" @click="showDialog = false" />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

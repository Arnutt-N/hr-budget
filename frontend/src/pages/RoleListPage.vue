<script setup lang="ts">
import { computed, ref } from 'vue'
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
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import ListEmptyState from '@/components/ListEmptyState.vue'
import { useDeleteConfirm } from '@/composables/useDeleteConfirm'
import type { Role, Permission } from '@/types/rbac'
import {
  useRoleList,
  useCreateRole,
  useUpdateRole,
  useDeleteRole,
} from '@/queries/useRoles'
import { usePermissionCatalogue } from '@/queries/usePermissions'
import { activeSeverity } from '@/lib/rbac'

const confirm = useConfirm()
const confirmDelete = useDeleteConfirm()
const toast = useToast()

const { data: roles, isLoading, isError, error } = useRoleList()
const { data: permissions } = usePermissionCatalogue()
const createMutation = useCreateRole()
const updateMutation = useUpdateRole()
const deleteMutation = useDeleteRole()

// Permission catalogue grouped by resource for the picker.
const ROOT_GROUP = 'อื่น ๆ'
const permGroups = computed<Record<string, Permission[]>>(() => {
  const groups: Record<string, Permission[]> = {}
  for (const p of permissions.value ?? []) {
    const key = p.resource ?? ROOT_GROUP
    ;(groups[key] ??= []).push(p)
  }
  return groups
})

// --- Create / edit dialog state ---
const showDialog = ref(false)
const editingRole = ref<Role | null>(null)
const selectedPerms = ref<string[]>([])
const formError = ref('')

const isEditing = computed(() => editingRole.value !== null)
const isSystem = computed(() => !!editingRole.value?.is_system)
const dialogTitle = computed(() =>
  !isEditing.value ? 'เพิ่มบทบาท' : isSystem.value ? 'ดูบทบาทระบบ' : 'แก้ไขบทบาท',
)
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

const CODE_RE = /^[a-z][a-z0-9_]{1,49}$/

const schema = toTypedSchema(
  z
    .object({
      code: z.string().min(1, 'กรุณาระบุรหัสบทบาท'),
      name_th: z.string().min(1, 'กรุณาระบุชื่อบทบาท'),
      name_en: z.string().optional(),
      description: z.string().optional(),
    })
    // The code format was only ever enforced on create (backend CreateRoleDto
    // likewise; code is not editable on update). Legacy rows may violate the
    // pattern, so skip the check when editing an existing role.
    .superRefine((values, ctx) => {
      if (!isEditing.value && !CODE_RE.test(values.code)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          path: ['code'],
          message: 'รหัสบทบาทต้องเป็น a-z, 0-9, _ ขึ้นต้นด้วยตัวอักษร (≤50)',
        })
      }
    }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [formCode] = defineField('code')
const [formNameTh] = defineField('name_th')
const [formNameEn] = defineField('name_en')
const [formDescription] = defineField('description')

function openCreate(): void {
  editingRole.value = null
  selectedPerms.value = []
  formError.value = ''
  resetForm({ values: { code: '', name_th: '', name_en: '', description: '' } })
  showDialog.value = true
}

function openEdit(role: Role): void {
  editingRole.value = role
  selectedPerms.value = [...role.permissions]
  formError.value = ''
  resetForm({
    values: {
      code: role.code,
      name_th: role.name_th,
      name_en: role.name_en ?? '',
      description: role.description ?? '',
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  formError.value = ''
  if (isSystem.value) {
    // System roles are view-only here (backend rejects edits).
    showDialog.value = false
    return
  }
  try {
    if (isEditing.value && editingRole.value) {
      await updateMutation.mutateAsync({
        id: editingRole.value.id,
        data: {
          name_th: values.name_th.trim(),
          name_en: values.name_en?.trim() || null,
          description: values.description?.trim() || null,
          permissions: selectedPerms.value,
        },
      })
      toast.add({ severity: 'success', summary: 'แก้ไขบทบาทสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync({
        code: values.code.trim(),
        name_th: values.name_th.trim(),
        name_en: values.name_en?.trim() || null,
        description: values.description?.trim() || null,
        permissions: selectedPerms.value,
      })
      toast.add({ severity: 'success', summary: 'สร้างบทบาทสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    formError.value = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
  }
})

function confirmToggle(role: Role): void {
  const turningOff = !!role.is_active
  confirm.require({
    message: turningOff
      ? `ปิดการใช้งานบทบาท "${role.name_th}"? ผู้ใช้ที่มีบทบาทนี้จะหมดสิทธิ์ที่ผูกไว้`
      : `เปิดการใช้งานบทบาท "${role.name_th}"?`,
    header: turningOff ? 'ยืนยันปิดบทบาท' : 'ยืนยันเปิดบทบาท',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: turningOff ? 'ปิดใช้งาน' : 'เปิดใช้งาน',
    rejectLabel: 'ยกเลิก',
    acceptClass: turningOff ? 'p-button-danger' : '',
    accept: async () => {
      try {
        await updateMutation.mutateAsync({ id: role.id, data: { is_active: !role.is_active } })
        toast.add({ severity: 'success', summary: 'อัปเดตสถานะบทบาทแล้ว', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'อัปเดตไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

function onDelete(role: Role): void {
  // Custom header/copy overrides the shared delete-confirm defaults via spread
  confirmDelete({
    header: 'ยืนยันลบบทบาท',
    message: `ลบบทบาท "${role.name_th}" อย่างถาวร? ผู้ใช้ที่ถูกมอบบทบาทนี้จะถูกถอนสิทธิ์`,
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(role.id)
        toast.add({ severity: 'success', summary: 'ลบบทบาทสำเร็จ', life: 3000 })
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
      title="บทบาทและสิทธิ์"
      subtitle="สร้าง/แก้บทบาท กำหนดชุดสิทธิ์ และเปิด/ปิดการใช้งาน"
    >
      <Button label="เพิ่มบทบาท" icon="pi pi-plus" @click="openCreate" />
    </PageHeader>

    <QueryErrorState v-if="isError" :error="error" />

    <div class="table-scroll" v-else>
    <DataTable
      :value="roles ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <ListEmptyState message="ยังไม่มีบทบาท" />
      </template>

      <Column header="บทบาท" sortable field="name_th">
        <template #body="{ data }">
          <div>
            <span class="font-medium text-white">{{ data.name_th }}</span>
            <span class="ml-2 text-xs text-dark-muted">{{ data.code }}</span>
          </div>
        </template>
      </Column>

      <Column header="ประเภท">
        <template #body="{ data }">
          <Tag
            :value="data.is_system ? 'ระบบ' : 'กำหนดเอง'"
            :severity="data.is_system ? 'warn' : 'info'"
          />
        </template>
      </Column>

      <Column header="สิทธิ์">
        <template #body="{ data }">
          <span class="text-sm text-dark-muted">{{ data.permissions.length }} สิทธิ์</span>
        </template>
      </Column>

      <Column header="สถานะ" sortable field="is_active">
        <template #body="{ data }">
          <Tag
            :value="data.is_active ? 'ใช้งาน' : 'ปิดใช้งาน'"
            :severity="activeSeverity(data.is_active)"
          />
        </template>
      </Column>

      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <div class="flex justify-end gap-1">
            <Button
              :label="data.is_system ? 'ดู' : 'แก้ไข'"
              size="small"
              text
              icon="pi pi-pencil"
              @click="openEdit(data)"
            />
            <Button
              :label="data.is_active ? 'ปิด' : 'เปิด'"
              size="small"
              text
              :severity="data.is_active ? 'danger' : 'success'"
              :disabled="!!data.is_system || updateMutation.isPending.value"
              :title="data.is_system ? 'บทบาทระบบปิดไม่ได้' : undefined"
              @click="confirmToggle(data)"
            />
            <Button
              v-if="!data.is_system"
              label="ลบ"
              size="small"
              text
              severity="danger"
              icon="pi pi-trash"
              @click="onDelete(data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>
    </div>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-2xl">
      <form class="space-y-4" @submit.prevent="onSave">
        <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>
        <Message v-if="isSystem" severity="warn" :closable="false">
          บทบาทระบบ – ดูได้อย่างเดียว แก้ไข/ลบไม่ได้
        </Message>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField id="role-code" label="รหัสบทบาท" :error="errors.code">
            <InputText
              id="role-code"
              v-model.trim="formCode"
              :disabled="isEditing"
              :invalid="!!errors.code"
              :aria-describedby="errors.code ? 'role-code-error' : undefined"
              placeholder="เช่น regional_supervisor"
              fluid
            />
            <small v-if="!errors.code && isEditing" class="text-dark-muted">รหัสบทบาทแก้ไขไม่ได้</small>
          </FormField>
          <FormField id="role-name-th" label="ชื่อ (ไทย)" :error="errors.name_th">
            <InputText
              id="role-name-th"
              v-model="formNameTh"
              :disabled="isSystem"
              :invalid="!!errors.name_th"
              :aria-describedby="errors.name_th ? 'role-name-th-error' : undefined"
              fluid
            />
          </FormField>
          <FormField id="role-name-en" label="ชื่อ (อังกฤษ)">
            <InputText id="role-name-en" v-model.trim="formNameEn" :disabled="isSystem" fluid />
          </FormField>
          <FormField id="role-desc" class="sm:col-span-2" label="คำอธิบาย">
            <Textarea id="role-desc" v-model="formDescription" :disabled="isSystem" rows="2" fluid />
          </FormField>
        </div>

        <div>
          <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium text-dark-muted">ชุดสิทธิ์</span>
            <span class="text-xs text-dark-muted">เลือกแล้ว {{ selectedPerms.length }} สิทธิ์</span>
          </div>
          <div class="max-h-72 space-y-4 overflow-y-auto rounded-lg border border-dark-border p-3">
            <div v-for="(perms, resource) in permGroups" :key="resource">
              <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-dark-muted">
                {{ resource }}
              </p>
              <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                <label
                  v-for="perm in perms"
                  :key="perm.code"
                  class="flex items-center gap-2 text-sm"
                >
                  <Checkbox
                    v-model="selectedPerms"
                    :value="perm.code"
                    :disabled="isSystem"
                    :input-id="`perm-${perm.code}`"
                  />
                  <span>{{ perm.name_th }}</span>
                  <span class="text-xs text-dark-muted">{{ perm.code }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button
            :label="isSystem ? 'ปิด' : 'ยกเลิก'"
            severity="secondary"
            text
            :disabled="saving"
            @click="showDialog = false"
          />
          <Button v-if="!isSystem" type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

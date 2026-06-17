<script setup lang="ts">
import { computed, ref } from 'vue'
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
const formCode = ref('')
const formNameTh = ref('')
const formNameEn = ref('')
const formDescription = ref('')
const selectedPerms = ref<string[]>([])
const formError = ref('')

const isEditing = computed(() => editingRole.value !== null)
const isSystem = computed(() => !!editingRole.value?.is_system)
const dialogTitle = computed(() =>
  !isEditing.value ? 'เพิ่มบทบาท' : isSystem.value ? 'ดูบทบาทระบบ' : 'แก้ไขบทบาท',
)
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

function openCreate(): void {
  editingRole.value = null
  formCode.value = ''
  formNameTh.value = ''
  formNameEn.value = ''
  formDescription.value = ''
  selectedPerms.value = []
  formError.value = ''
  showDialog.value = true
}

function openEdit(role: Role): void {
  editingRole.value = role
  formCode.value = role.code
  formNameTh.value = role.name_th
  formNameEn.value = role.name_en ?? ''
  formDescription.value = role.description ?? ''
  selectedPerms.value = [...role.permissions]
  formError.value = ''
  showDialog.value = true
}

const CODE_RE = /^[a-z][a-z0-9_]{1,49}$/

async function onSave(): Promise<void> {
  formError.value = ''
  if (isSystem.value) {
    // System roles are view-only here (backend rejects edits).
    showDialog.value = false
    return
  }
  if (!isEditing.value && !CODE_RE.test(formCode.value)) {
    formError.value = 'รหัสบทบาทต้องเป็น a-z, 0-9, _ ขึ้นต้นด้วยตัวอักษร (≤50)'
    return
  }
  if (!formNameTh.value.trim()) {
    formError.value = 'กรุณาระบุชื่อบทบาท'
    return
  }
  try {
    if (isEditing.value && editingRole.value) {
      await updateMutation.mutateAsync({
        id: editingRole.value.id,
        data: {
          name_th: formNameTh.value.trim(),
          name_en: formNameEn.value.trim() || null,
          description: formDescription.value.trim() || null,
          permissions: selectedPerms.value,
        },
      })
      toast.add({ severity: 'success', summary: 'แก้ไขบทบาทสำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync({
        code: formCode.value.trim(),
        name_th: formNameTh.value.trim(),
        name_en: formNameEn.value.trim() || null,
        description: formDescription.value.trim() || null,
        permissions: selectedPerms.value,
      })
      toast.add({ severity: 'success', summary: 'สร้างบทบาทสำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    formError.value = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
  }
}

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

function confirmDelete(role: Role): void {
  confirm.require({
    message: `ลบบทบาท "${role.name_th}" อย่างถาวร? ผู้ใช้ที่ถูกมอบบทบาทนี้จะถูกถอนสิทธิ์`,
    header: 'ยืนยันลบบทบาท',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
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
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">บทบาทและสิทธิ์</h1>
        <p class="mt-1 text-sm text-dark-muted">
          สร้าง/แก้บทบาท กำหนดชุดสิทธิ์ และเปิด/ปิดการใช้งาน
        </p>
      </div>
      <Button label="เพิ่มบทบาท" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="roles ?? []"
      :loading="isLoading"
      paginator
      :rows="15"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีบทบาท</p>
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
              @click="confirmToggle(data)"
            />
            <Button
              v-if="!data.is_system"
              label="ลบ"
              size="small"
              text
              severity="danger"
              icon="pi pi-trash"
              @click="confirmDelete(data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" :header="dialogTitle" modal class="w-full max-w-2xl">
      <form class="space-y-4" @submit.prevent="onSave">
        <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>
        <Message v-if="isSystem" severity="warn" :closable="false">
          บทบาทระบบ — ดูได้อย่างเดียว แก้ไข/ลบไม่ได้
        </Message>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="flex flex-col gap-1">
            <label for="role-code" class="text-sm font-medium text-dark-muted">รหัสบทบาท</label>
            <InputText
              id="role-code"
              v-model.trim="formCode"
              :disabled="isEditing"
              placeholder="เช่น regional_supervisor"
              fluid
            />
            <small v-if="isEditing" class="text-dark-muted">รหัสบทบาทแก้ไขไม่ได้</small>
          </div>
          <div class="flex flex-col gap-1">
            <label for="role-name-th" class="text-sm font-medium text-dark-muted">ชื่อ (ไทย)</label>
            <InputText id="role-name-th" v-model="formNameTh" :disabled="isSystem" fluid />
          </div>
          <div class="flex flex-col gap-1">
            <label for="role-name-en" class="text-sm font-medium text-dark-muted">ชื่อ (อังกฤษ)</label>
            <InputText id="role-name-en" v-model.trim="formNameEn" :disabled="isSystem" fluid />
          </div>
          <div class="flex flex-col gap-1 sm:col-span-2">
            <label for="role-desc" class="text-sm font-medium text-dark-muted">คำอธิบาย</label>
            <Textarea id="role-desc" v-model="formDescription" :disabled="isSystem" rows="2" fluid />
          </div>
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

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
import Password from 'primevue/password'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { User, UpdateUser } from '@/types/user'
import { useUserList, useCreateUser, useUpdateUser, useDeleteUser } from '@/queries/useUsers'

const confirm = useConfirm()
const toast = useToast()

const { data: users, isLoading, isError, error } = useUserList()
const createMutation = useCreateUser()
const updateMutation = useUpdateUser()
const deleteMutation = useDeleteUser()

const roleOptions = ['admin', 'editor', 'viewer']

const showDialog = ref(false)
const editingId = ref<number | null>(null)
const dialogTitle = computed(() => (editingId.value ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้'))
const saving = computed(() => createMutation.isPending.value || updateMutation.isPending.value)

// Password is required on create only — on edit, leaving it blank keeps the old one
const schema = toTypedSchema(
  z
    .object({
      email: z.string({ required_error: 'กรุณากรอกอีเมล' }).min(1, 'กรุณากรอกอีเมล').email('รูปแบบอีเมลไม่ถูกต้อง'),
      password: z.string().optional(),
      name: z.string({ required_error: 'กรุณากรอกชื่อ' }).min(1, 'กรุณากรอกชื่อ'),
      role: z.string({ required_error: 'กรุณาเลือกบทบาท' }).min(1, 'กรุณาเลือกบทบาท'),
      department: z.string().optional(),
      is_active: z.boolean().optional(),
    })
    .superRefine((data, ctx) => {
      if (!editingId.value && !data.password) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'กรุณากรอกรหัสผ่าน' })
      }
      if (data.password && data.password.length > 0 && data.password.length < 8) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร' })
      }
    }),
)

const { defineField, handleSubmit, errors, resetForm } = useForm({ validationSchema: schema })
const [email] = defineField('email')
const [password] = defineField('password')
const [name] = defineField('name')
const [role] = defineField('role')
const [department] = defineField('department')
const [isActive] = defineField('is_active')

function openCreate(): void {
  editingId.value = null
  resetForm({ values: { email: '', password: '', name: '', role: 'viewer', department: '', is_active: true } })
  showDialog.value = true
}

function openEdit(user: User): void {
  editingId.value = user.id
  resetForm({
    values: {
      email: user.email,
      password: '',
      name: user.name,
      role: user.role,
      department: user.department ?? '',
      is_active: !!user.is_active,
    },
  })
  showDialog.value = true
}

const onSave = handleSubmit(async (values) => {
  try {
    if (editingId.value) {
      // Never send an empty password on update — omit the key entirely
      const { password: pwd, ...rest } = values
      const data: UpdateUser = pwd ? { ...rest, password: pwd } : rest
      await updateMutation.mutateAsync({ id: editingId.value, data })
      toast.add({ severity: 'success', summary: 'แก้ไขผู้ใช้สำเร็จ', life: 3000 })
    } else {
      await createMutation.mutateAsync({ ...values, password: values.password ?? '' })
      toast.add({ severity: 'success', summary: 'เพิ่มผู้ใช้สำเร็จ', life: 3000 })
    }
    showDialog.value = false
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
})

function confirmDelete(user: User): void {
  confirm.require({
    message: `ยืนยันลบผู้ใช้ "${user.name}"?`,
    header: 'ยืนยันการลบ',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ลบ',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(user.id)
        toast.add({ severity: 'success', summary: 'ลบผู้ใช้สำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ลบไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}

function roleSeverity(r: string): string {
  if (r === 'admin') return 'danger'
  if (r === 'editor') return 'info'
  return 'secondary'
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">จัดการผู้ใช้</h1>
      <Button label="เพิ่มผู้ใช้" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="users ?? []"
      :loading="isLoading"
      paginator
      :rows="10"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีข้อมูลผู้ใช้</p>
      </template>

      <Column field="email" header="อีเมล" sortable>
        <template #body="{ data }">
          <span class="text-sm">{{ data.email }}</span>
        </template>
      </Column>
      <Column field="name" header="ชื่อ" sortable>
        <template #body="{ data }">
          <span class="font-medium">{{ data.name }}</span>
        </template>
      </Column>
      <Column field="role" header="บทบาท" sortable>
        <template #body="{ data }">
          <Tag :value="data.role" :severity="roleSeverity(data.role)" />
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
          <label for="user-email" class="text-sm font-medium text-dark-muted">อีเมล</label>
          <InputText id="user-email" v-model.trim="email" type="email" :invalid="!!errors.email" fluid />
          <small v-if="errors.email" class="text-red-600" role="alert">{{ errors.email }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="user-password" class="text-sm font-medium text-dark-muted">
            {{ editingId ? 'รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)' : 'รหัสผ่าน' }}
          </label>
          <Password
            v-model="password"
            input-id="user-password"
            :feedback="false"
            :invalid="!!errors.password"
            toggle-mask
            fluid
          />
          <small v-if="errors.password" class="text-red-600" role="alert">{{ errors.password }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="user-name" class="text-sm font-medium text-dark-muted">ชื่อ</label>
          <InputText id="user-name" v-model.trim="name" :invalid="!!errors.name" fluid />
          <small v-if="errors.name" class="text-red-600" role="alert">{{ errors.name }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="user-role" class="text-sm font-medium text-dark-muted">บทบาท</label>
          <Select v-model="role" label-id="user-role" :options="roleOptions" :invalid="!!errors.role" fluid />
          <small v-if="errors.role" class="text-red-600" role="alert">{{ errors.role }}</small>
        </div>

        <div class="flex flex-col gap-1">
          <label for="user-department" class="text-sm font-medium text-dark-muted">แผนก</label>
          <InputText id="user-department" v-model.trim="department" fluid />
        </div>

        <label class="flex items-center gap-2 text-sm">
          <Checkbox v-model="isActive" binary input-id="user-is-active" />
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

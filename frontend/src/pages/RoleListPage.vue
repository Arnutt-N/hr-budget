<script setup lang="ts">
import { ref } from 'vue'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { Role } from '@/types/rbac'
import { useRoleList, useUpdateRole } from '@/queries/useRoles'
import { activeSeverity } from '@/lib/rbac'

const confirm = useConfirm()
const toast = useToast()

const { data: roles, isLoading, isError, error } = useRoleList()
const updateMutation = useUpdateRole()

// Permission-list dialog
const showPerms = ref(false)
const selectedRole = ref<Role | null>(null)

function openPermissions(role: Role): void {
  selectedRole.value = role
  showPerms.value = true
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
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">บทบาทและสิทธิ์</h1>
        <p class="mt-1 text-sm text-dark-muted">
          เปิด/ปิดการใช้งานบทบาท และดูชุดสิทธิ์ของแต่ละบทบาท
        </p>
      </div>
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
          <Button
            :label="`${data.permissions.length} สิทธิ์`"
            size="small"
            text
            icon="pi pi-key"
            @click="openPermissions(data)"
          />
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
          <Button
            :label="data.is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน'"
            size="small"
            text
            :severity="data.is_active ? 'danger' : 'success'"
            :disabled="!!data.is_system || updateMutation.isPending.value"
            @click="confirmToggle(data)"
          />
          <!-- System roles cannot be disabled (backend rejects); toggle hidden via disabled -->
          <span v-if="data.is_system" class="ml-1 text-xs text-dark-muted">บทบาทระบบ</span>
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="showPerms"
      :header="`สิทธิ์ของบทบาท: ${selectedRole?.name_th ?? ''}`"
      modal
      class="w-full max-w-lg"
    >
      <div v-if="selectedRole && selectedRole.permissions.length" class="flex flex-wrap gap-2">
        <Tag
          v-for="perm in selectedRole.permissions"
          :key="perm"
          :value="perm"
          severity="secondary"
        />
      </div>
      <p v-else class="text-sm text-dark-muted">บทบาทนี้ยังไม่มีสิทธิ์ที่กำหนด</p>
    </Dialog>
  </div>
</template>

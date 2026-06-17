<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import type { AccessGrant, ScopeType, AssignGrantPayload } from '@/types/rbac'
import { useUserGrants, useCreateGrant, useDeleteGrant } from '@/queries/useAccessGrants'
import { useRoleList } from '@/queries/useRoles'
import { useUserList } from '@/queries/useUsers'
import { useOrganizationList } from '@/queries/useOrganizations'
import { scopeTypeLabel, activeSeverity } from '@/lib/rbac'

const route = useRoute()
const confirm = useConfirm()
const toast = useToast()

// Guard against a non-numeric param (/users/abc/...): fall back to 0 so the
// query's `enabled: id > 0` short-circuits instead of requesting /users/NaN/…
const userId = computed(() => {
  const n = Number(route.params.id)
  return Number.isFinite(n) && n > 0 ? n : 0
})

const { data: grants, isLoading, isError, error } = useUserGrants(userId)
const { data: roles } = useRoleList()
const { data: users } = useUserList()
const { data: orgs } = useOrganizationList()
const createMutation = useCreateGrant(userId)
const deleteMutation = useDeleteGrant(userId)

const userName = computed(() => users.value?.find((u) => u.id === userId.value)?.name ?? `#${userId.value}`)

// Only active roles are assignable.
const activeRoles = computed(() => (roles.value ?? []).filter((r) => r.is_active))
const orgOptions = computed(() => (orgs.value ?? []).filter((o) => o.is_active))

// Scope types surfaced in the UI. category/region are deferred (backend rejects
// them for non-super actors and org-level data has no category dimension yet).
const scopeOptions: { label: string; value: ScopeType }[] = [
  { label: scopeTypeLabel('organization'), value: 'organization' },
  { label: scopeTypeLabel('all'), value: 'all' },
]

const showDialog = ref(false)
const formRoleId = ref<number | null>(null)
const formScopeType = ref<ScopeType>('organization')
const formOrgId = ref<number | null>(null)
const formError = ref('')

const needsOrg = computed(() => formScopeType.value === 'organization')
const saving = computed(() => createMutation.isPending.value)

function openCreate(): void {
  formRoleId.value = null
  formScopeType.value = 'organization'
  formOrgId.value = null
  formError.value = ''
  showDialog.value = true
}

async function onSave(): Promise<void> {
  formError.value = ''
  if (!formRoleId.value) {
    formError.value = 'กรุณาเลือกบทบาท'
    return
  }
  if (needsOrg.value && !formOrgId.value) {
    formError.value = 'กรุณาเลือกหน่วยงาน'
    return
  }
  const payload: AssignGrantPayload = {
    role_id: formRoleId.value,
    scope_type: formScopeType.value,
    scope_ref_id: needsOrg.value ? formOrgId.value : null,
  }
  try {
    await createMutation.mutateAsync(payload)
    toast.add({ severity: 'success', summary: 'มอบสิทธิ์สำเร็จ', life: 3000 })
    showDialog.value = false
  } catch (e: unknown) {
    formError.value = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
  }
}

function scopeRefLabel(g: AccessGrant): string {
  if (g.scope_type === 'all') return '—'
  if (g.scope_type === 'organization') {
    const org = orgs.value?.find((o) => o.id === g.scope_ref_id)
    return org ? org.name_th : `#${g.scope_ref_id}`
  }
  return g.scope_ref_id !== null ? `#${g.scope_ref_id}` : '—'
}

function confirmRevoke(g: AccessGrant): void {
  confirm.require({
    message: `ถอนบทบาท "${g.role_name_th}" (${scopeTypeLabel(g.scope_type)}) ออกจากผู้ใช้นี้?`,
    header: 'ยืนยันถอนสิทธิ์',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ถอนสิทธิ์',
    rejectLabel: 'ยกเลิก',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await deleteMutation.mutateAsync(g.id)
        toast.add({ severity: 'success', summary: 'ถอนสิทธิ์สำเร็จ', life: 3000 })
      } catch (e: unknown) {
        const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
        toast.add({ severity: 'error', summary: 'ถอนสิทธิ์ไม่สำเร็จ', detail: message, life: 5000 })
      }
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">สิทธิ์การเข้าถึง</h1>
        <p class="mt-1 text-sm text-dark-muted">ผู้ใช้: {{ userName }}</p>
      </div>
      <div class="flex gap-2">
        <router-link to="/users" class="self-center text-sm text-dark-muted hover:text-dark-text">
          &larr; กลับ
        </router-link>
        <Button label="มอบบทบาท/สิทธิ์" icon="pi pi-plus" @click="openCreate" />
      </div>
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="grants ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ผู้ใช้นี้ยังไม่มีบทบาท/สิทธิ์ที่มอบไว้</p>
      </template>

      <Column header="บทบาท">
        <template #body="{ data }">
          <span class="font-medium text-white">{{ data.role_name_th }}</span>
          <span class="ml-2 text-xs text-dark-muted">{{ data.role_code }}</span>
        </template>
      </Column>
      <Column header="ขอบเขต">
        <template #body="{ data }">
          <Tag :value="scopeTypeLabel(data.scope_type)" severity="info" />
        </template>
      </Column>
      <Column header="อ้างอิง">
        <template #body="{ data }">
          <span class="text-sm">{{ scopeRefLabel(data) }}</span>
        </template>
      </Column>
      <Column header="สถานะ">
        <template #body="{ data }">
          <Tag
            :value="data.is_active ? 'ใช้งาน' : 'ปิด'"
            :severity="activeSeverity(data.is_active)"
          />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button
            label="ถอนสิทธิ์"
            size="small"
            text
            severity="danger"
            @click="confirmRevoke(data)"
          />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="showDialog" header="มอบบทบาท/สิทธิ์" modal class="w-full max-w-md">
      <form class="space-y-4" @submit.prevent="onSave">
        <Message v-if="formError" severity="error" :closable="false">{{ formError }}</Message>

        <div class="flex flex-col gap-1">
          <label for="grant-role" class="text-sm font-medium text-dark-muted">บทบาท</label>
          <Select
            v-model="formRoleId"
            label-id="grant-role"
            :options="activeRoles"
            option-label="name_th"
            option-value="id"
            placeholder="เลือกบทบาท"
            filter
            fluid
          />
        </div>

        <div class="flex flex-col gap-1">
          <label for="grant-scope" class="text-sm font-medium text-dark-muted">ขอบเขตสิทธิ์</label>
          <Select
            v-model="formScopeType"
            label-id="grant-scope"
            :options="scopeOptions"
            option-label="label"
            option-value="value"
            fluid
          />
        </div>

        <div v-if="needsOrg" class="flex flex-col gap-1">
          <label for="grant-org" class="text-sm font-medium text-dark-muted">หน่วยงาน</label>
          <Select
            v-model="formOrgId"
            label-id="grant-org"
            :options="orgOptions"
            option-label="name_th"
            option-value="id"
            placeholder="เลือกหน่วยงาน"
            filter
            fluid
          />
          <small class="text-dark-muted">สิทธิ์จะครอบคลุมหน่วยงานนี้และหน่วยงานลูกทั้งหมด</small>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button
            label="ยกเลิก"
            severity="secondary"
            text
            :disabled="saving"
            @click="showDialog = false"
          />
          <Button type="submit" label="บันทึก" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

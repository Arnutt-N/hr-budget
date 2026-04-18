<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOrganizationStore } from '@/stores/organizations'
import type { Organization, CreateOrganization } from '@/types/organization'

const store = useOrganizationStore()

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<CreateOrganization>({ code: '', name_th: '' })

const orgTypes = ['ministry', 'department', 'division', 'section', 'province', 'office']

onMounted(() => { store.fetchList() })

function openCreate() {
  editingId.value = null
  form.value = { code: '', name_th: '' }
  showModal.value = true
}

function openEdit(org: Organization) {
  editingId.value = org.id
  form.value = {
    code: org.code,
    name_th: org.name_th,
    abbreviation: org.abbreviation ?? undefined,
    org_type: org.org_type ?? undefined,
    region: org.region ?? undefined,
    parent_id: org.parent_id ?? undefined,
  }
  showModal.value = true
}

async function save() {
  let result: { ok: boolean; error?: string }
  if (editingId.value) {
    result = await store.update(editingId.value, form.value)
  } else {
    result = await store.create(form.value)
  }
  if (result.ok) {
    showModal.value = false
  } else {
    alert(result.error ?? 'เกิดข้อผิดพลาด')
  }
}

async function remove(id: number) {
  if (confirm('ยืนยันลบหน่วยงาน?')) {
    await store.remove(id)
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">หน่วยงาน</h1>
      <button @click="openCreate" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + เพิ่มหน่วยงาน
      </button>
    </div>

    <div v-if="store.loading" class="py-8 text-center text-gray-500">กำลังโหลด...</div>

    <table v-else class="w-full overflow-hidden rounded-lg bg-white shadow">
      <thead class="bg-gray-50 text-left text-sm text-gray-600">
        <tr>
          <th class="px-4 py-3">รหัส</th>
          <th class="px-4 py-3">ชื่อหน่วยงาน</th>
          <th class="px-4 py-3">ประเภท</th>
          <th class="px-4 py-3">สถานะ</th>
          <th class="px-4 py-3 text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="org in store.organizations" :key="org.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 font-mono text-sm">{{ org.code }}</td>
          <td class="px-4 py-3">{{ org.name_th }}</td>
          <td class="px-4 py-3 text-sm text-gray-600">{{ org.org_type ?? '-' }}</td>
          <td class="px-4 py-3">
            <span :class="org.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-0.5 text-xs font-medium">
              {{ org.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
            </span>
          </td>
          <td class="space-x-2 px-4 py-3 text-right">
            <button @click="openEdit(org)" class="text-xs text-blue-600 hover:underline">แก้ไข</button>
            <button @click="remove(org.id)" class="text-xs text-red-600 hover:underline">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="mb-4 text-lg font-bold">{{ editingId ? 'แก้ไขหน่วยงาน' : 'เพิ่มหน่วยงาน' }}</h2>
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">รหัสหน่วยงาน</label>
            <input v-model="form.code" type="text" class="w-full rounded border px-3 py-2" maxlength="50" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อหน่วยงาน</label>
            <input v-model="form.name_th" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อย่อ</label>
            <input v-model="form.abbreviation" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ประเภท</label>
            <select v-model="form.org_type" class="w-full rounded border px-3 py-2">
              <option value="">-- เลือก --</option>
              <option v-for="t in orgTypes" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="showModal = false" class="rounded border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">ยกเลิก</button>
          <button @click="save" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
</template>

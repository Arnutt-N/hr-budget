<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/users'
import type { User, CreateUser, UpdateUser } from '@/types/user'

const store = useUserStore()

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<CreateUser>({ email: '', password: '', name: '', role: 'viewer' })

const roles = ['admin', 'editor', 'viewer']

onMounted(() => { store.fetchList() })

function openCreate() {
  editingId.value = null
  form.value = { email: '', password: '', name: '', role: 'viewer' }
  showModal.value = true
}

function openEdit(user: User) {
  editingId.value = user.id
  form.value = {
    email: user.email,
    password: '',
    name: user.name,
    role: user.role,
    is_active: !!user.is_active,
    department: user.department ?? undefined,
  }
  showModal.value = true
}

async function save() {
  let result: { ok: boolean; error?: string }
  if (editingId.value) {
    const { password, ...rest } = form.value
    const data: UpdateUser = password ? { ...rest, password } : rest
    result = await store.update(editingId.value, data)
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
  if (confirm('ยืนยันลบผู้ใช้?')) {
    await store.remove(id)
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">จัดการผู้ใช้</h1>
      <button @click="openCreate" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + เพิ่มผู้ใช้
      </button>
    </div>

    <div v-if="store.loading" class="py-8 text-center text-gray-500">กำลังโหลด...</div>

    <table v-else class="w-full overflow-hidden rounded-lg bg-white shadow">
      <thead class="bg-gray-50 text-left text-sm text-gray-600">
        <tr>
          <th class="px-4 py-3">อีเมล</th>
          <th class="px-4 py-3">ชื่อ</th>
          <th class="px-4 py-3">บทบาท</th>
          <th class="px-4 py-3">สถานะ</th>
          <th class="px-4 py-3 text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="user in store.users" :key="user.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm">{{ user.email }}</td>
          <td class="px-4 py-3">{{ user.name }}</td>
          <td class="px-4 py-3">
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">{{ user.role }}</span>
          </td>
          <td class="px-4 py-3">
            <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-0.5 text-xs font-medium">
              {{ user.is_active ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
            </span>
          </td>
          <td class="space-x-2 px-4 py-3 text-right">
            <button @click="openEdit(user)" class="text-xs text-blue-600 hover:underline">แก้ไข</button>
            <button @click="remove(user.id)" class="text-xs text-red-600 hover:underline">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="mb-4 text-lg font-bold">{{ editingId ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้' }}</h2>
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">อีเมล</label>
            <input v-model="form.email" type="email" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">{{ editingId ? 'รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)' : 'รหัสผ่าน' }}</label>
            <input v-model="form.password" type="password" class="w-full rounded border px-3 py-2" :required="!editingId" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อ</label>
            <input v-model="form.name" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">บทบาท</label>
            <select v-model="form.role" class="w-full rounded border px-3 py-2">
              <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">แผนก</label>
            <input v-model="form.department" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.is_active" type="checkbox" class="rounded" />
            ใช้งาน
          </label>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="showModal = false" class="rounded border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">ยกเลิก</button>
          <button @click="save" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
</template>

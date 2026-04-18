<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFiscalYearStore } from '@/stores/fiscalYears'
import type { FiscalYear, CreateFiscalYear } from '@/types/fiscal-year'

const store = useFiscalYearStore()

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<CreateFiscalYear>({ year: 0, start_date: '', end_date: '' })

onMounted(() => { store.fetchList() })

function openCreate() {
  editingId.value = null
  form.value = { year: new Date().getFullYear() + 543, start_date: '', end_date: '' }
  showModal.value = true
}

function openEdit(fy: FiscalYear) {
  editingId.value = fy.id
  form.value = { year: fy.year, start_date: fy.start_date, end_date: fy.end_date, is_current: !!fy.is_current }
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
  if (confirm('ยืนยันลบปีงบประมาณ?')) {
    await store.remove(id)
  }
}

async function setCurrent(id: number) {
  await store.setCurrent(id)
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">ปีงบประมาณ</h1>
      <button @click="openCreate" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + เพิ่มปีงบประมาณ
      </button>
    </div>

    <div v-if="store.loading" class="py-8 text-center text-gray-500">กำลังโหลด...</div>

    <table v-else class="w-full overflow-hidden rounded-lg bg-white shadow">
      <thead class="bg-gray-50 text-left text-sm text-gray-600">
        <tr>
          <th class="px-4 py-3">ปีงบประมาณ</th>
          <th class="px-4 py-3">วันเริ่มต้น</th>
          <th class="px-4 py-3">วันสิ้นสุด</th>
          <th class="px-4 py-3">สถานะ</th>
          <th class="px-4 py-3 text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="fy in store.fiscalYears" :key="fy.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 font-medium">{{ fy.year }}</td>
          <td class="px-4 py-3">{{ fy.start_date }}</td>
          <td class="px-4 py-3">{{ fy.end_date }}</td>
          <td class="px-4 py-3">
            <span v-if="fy.is_current" class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">ปีปัจจุบัน</span>
            <span v-else-if="fy.is_closed" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">ปิดแล้ว</span>
            <span v-else class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">เปิดใช้</span>
          </td>
          <td class="space-x-2 px-4 py-3 text-right">
            <button v-if="!fy.is_current" @click="setCurrent(fy.id)" class="text-xs text-green-600 hover:underline">ตั้งเป็นปีปัจจุบัน</button>
            <button @click="openEdit(fy)" class="text-xs text-blue-600 hover:underline">แก้ไข</button>
            <button @click="remove(fy.id)" class="text-xs text-red-600 hover:underline">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="mb-4 text-lg font-bold">{{ editingId ? 'แก้ไขปีงบประมาณ' : 'เพิ่มปีงบประมาณ' }}</h2>
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ปีงบประมาณ (พ.ศ.)</label>
            <input v-model.number="form.year" type="number" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">วันเริ่มต้น</label>
            <input v-model="form.start_date" type="date" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">วันสิ้นสุด</label>
            <input v-model="form.end_date" type="date" class="w-full rounded border px-3 py-2" />
          </div>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.is_current" type="checkbox" class="rounded" />
            ตั้งเป็นปีงบประมาณปัจจุบัน
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

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBudgetCategoryStore } from '@/stores/budgetCategories'
import type { BudgetCategory, BudgetCategoryItem, CreateCategory, CreateCategoryItem } from '@/types/budget-category'

const store = useBudgetCategoryStore()

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = ref<CreateCategory>({ code: '', name_th: '' })

const showItemModal = ref(false)
const editingItemId = ref<number | null>(null)
const itemForm = ref<CreateCategoryItem>({ name: '' })
const selectedCategoryId = ref<number | null>(null)

onMounted(() => { store.fetchList() })

function openCreate() {
  editingId.value = null
  form.value = { code: '', name_th: '' }
  showModal.value = true
}

function openEdit(cat: BudgetCategory) {
  editingId.value = cat.id
  form.value = { code: cat.code, name_th: cat.name_th, name_en: cat.name_en ?? undefined, sort_order: cat.sort_order ?? undefined }
  showModal.value = true
}

async function save() {
  let result: { ok: boolean; error?: string }
  if (editingId.value) {
    result = await store.updateCat(editingId.value, form.value)
  } else {
    result = await store.createCat(form.value)
  }
  if (result.ok) {
    showModal.value = false
  } else {
    alert(result.error ?? 'เกิดข้อผิดพลาด')
  }
}

async function removeCat(id: number) {
  if (confirm('ยืนยันลบหมวด? รายการทั้งหมดในหมวดจะถูกลบด้วย')) {
    await store.removeCat(id)
  }
}

async function viewItems(catId: number) {
  selectedCategoryId.value = catId
  await store.fetchItems(catId)
}

function openCreateItem() {
  editingItemId.value = null
  itemForm.value = { name: '' }
  showItemModal.value = true
}

function openEditItem(item: BudgetCategoryItem) {
  editingItemId.value = item.id
  itemForm.value = { name: item.name, code: item.code ?? undefined, level: item.level, sort_order: item.sort_order, is_active: !!item.is_active }
  showItemModal.value = true
}

async function saveItem() {
  if (!selectedCategoryId.value) return
  if (editingItemId.value) {
    await store.updateItem(selectedCategoryId.value, editingItemId.value, itemForm.value)
  } else {
    await store.createItem(selectedCategoryId.value, itemForm.value)
  }
  showItemModal.value = false
}

async function removeItem(itemId: number) {
  if (!selectedCategoryId.value) return
  if (confirm('ยืนยันลบรายการ?')) {
    await store.removeItem(selectedCategoryId.value, itemId)
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">หมวดงบประมาณ</h1>
      <button @click="openCreate" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + เพิ่มหมวด
      </button>
    </div>

    <div v-if="store.loading" class="py-8 text-center text-gray-500">กำลังโหลด...</div>

    <table v-else class="w-full overflow-hidden rounded-lg bg-white shadow">
      <thead class="bg-gray-50 text-left text-sm text-gray-600">
        <tr>
          <th class="px-4 py-3">รหัส</th>
          <th class="px-4 py-3">ชื่อหมวด</th>
          <th class="px-4 py-3">ระดับ</th>
          <th class="px-4 py-3 text-right">จัดการ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr v-for="cat in store.categories" :key="cat.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 font-mono text-sm">{{ cat.code }}</td>
          <td class="px-4 py-3">{{ cat.name_th }}</td>
          <td class="px-4 py-3 text-sm">{{ cat.level }}</td>
          <td class="space-x-2 px-4 py-3 text-right">
            <button @click="viewItems(cat.id)" class="text-xs text-green-600 hover:underline">รายการ</button>
            <button @click="openEdit(cat)" class="text-xs text-blue-600 hover:underline">แก้ไข</button>
            <button @click="removeCat(cat.id)" class="text-xs text-red-600 hover:underline">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Items panel -->
    <div v-if="selectedCategoryId" class="mt-6 rounded-lg bg-white p-4 shadow">
      <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold">รายการในหมวด</h2>
        <button @click="openCreateItem" class="rounded bg-green-600 px-3 py-1.5 text-sm text-white hover:bg-green-700">+ เพิ่มรายการ</button>
      </div>
      <table class="w-full">
        <thead class="bg-gray-50 text-left text-xs text-gray-600">
          <tr>
            <th class="px-3 py-2">ชื่อ</th>
            <th class="px-3 py-2">ระดับ</th>
            <th class="px-3 py-2">ลำดับ</th>
            <th class="px-3 py-2 text-right">จัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          <tr v-for="item in store.currentItems" :key="item.id" class="hover:bg-gray-50">
            <td class="px-3 py-2" :style="{ paddingLeft: (item.level * 20 + 12) + 'px' }">{{ item.name }}</td>
            <td class="px-3 py-2">{{ item.level }}</td>
            <td class="px-3 py-2">{{ item.sort_order }}</td>
            <td class="space-x-2 px-3 py-2 text-right">
              <button @click="openEditItem(item)" class="text-xs text-blue-600 hover:underline">แก้ไข</button>
              <button @click="removeItem(item.id)" class="text-xs text-red-600 hover:underline">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Category Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="mb-4 text-lg font-bold">{{ editingId ? 'แก้ไขหมวด' : 'เพิ่มหมวด' }}</h2>
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">รหัส</label>
            <input v-model="form.code" type="text" class="w-full rounded border px-3 py-2" maxlength="20" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อหมวด (ไทย)</label>
            <input v-model="form.name_th" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อหมวด (อังกฤษ)</label>
            <input v-model="form.name_en" type="text" class="w-full rounded border px-3 py-2" />
          </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="showModal = false" class="rounded border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">ยกเลิก</button>
          <button @click="save" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">บันทึก</button>
        </div>
      </div>
    </div>

    <!-- Item Modal -->
    <div v-if="showItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showItemModal = false">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="mb-4 text-lg font-bold">{{ editingItemId ? 'แก้ไขรายการ' : 'เพิ่มรายการ' }}</h2>
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อรายการ</label>
            <input v-model="itemForm.name" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">รหัส</label>
            <input v-model="itemForm.code" type="text" class="w-full rounded border px-3 py-2" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">ระดับ</label>
              <input v-model.number="itemForm.level" type="number" class="w-full rounded border px-3 py-2" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-gray-700">ลำดับ</label>
              <input v-model.number="itemForm.sort_order" type="number" class="w-full rounded border px-3 py-2" />
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="showItemModal = false" class="rounded border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">ยกเลิก</button>
          <button @click="saveItem" class="rounded bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
</template>

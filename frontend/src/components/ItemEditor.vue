<script setup lang="ts">
import { computed } from 'vue'

export interface ItemRow {
  item_name: string
  quantity: string
  unit_price: string
  remark: string | null
  category_item_id: number | null
}

const props = defineModel<ItemRow[]>({ required: true })
const items = computed(() => props.value)

const totalAmount = computed(() =>
  items.value.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0),
)

function addItem() {
  const updated = [...items.value, { item_name: '', quantity: '0', unit_price: '0', remark: null, category_item_id: null }]
  props.value = updated
}

function removeItem(index: number) {
  const updated = items.value.filter((_, i) => i !== index)
  props.value = updated
}

function updateField(index: number, field: keyof ItemRow, value: string | number | null) {
  const updated = items.value.map((item, i) =>
    i === index ? { ...item, [field]: value } : item,
  )
  props.value = updated
}

function formatAmount(qty: string, price: string): string {
  const amount = (parseFloat(qty) || 0) * (parseFloat(price) || 0)
  return amount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ชื่อรายการ</th>
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 w-24">จำนวน</th>
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 w-32">ราคาหน่วย</th>
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 w-32">จำนวนเงิน</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">หมายเหตุ</th>
            <th class="px-3 py-2 w-16"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(item, index) in items" :key="index">
            <td class="px-3 py-2">
              <input
                type="text"
                :value="item.item_name"
                @input="updateField(index, 'item_name', ($event.target as HTMLInputElement).value)"
                class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none"
                placeholder="ชื่อรายการ"
              />
            </td>
            <td class="px-3 py-2">
              <input
                type="number"
                :value="item.quantity"
                @input="updateField(index, 'quantity', ($event.target as HTMLInputElement).value)"
                class="w-full rounded border border-gray-300 px-2 py-1 text-sm text-right focus:border-blue-500 focus:outline-none"
                min="0"
                step="any"
              />
            </td>
            <td class="px-3 py-2">
              <input
                type="number"
                :value="item.unit_price"
                @input="updateField(index, 'unit_price', ($event.target as HTMLInputElement).value)"
                class="w-full rounded border border-gray-300 px-2 py-1 text-sm text-right focus:border-blue-500 focus:outline-none"
                min="0"
                step="any"
              />
            </td>
            <td class="px-3 py-2 text-right text-sm text-gray-700 whitespace-nowrap">
              {{ formatAmount(item.quantity, item.unit_price) }}
            </td>
            <td class="px-3 py-2">
              <input
                type="text"
                :value="item.remark ?? ''"
                @input="updateField(index, 'remark', ($event.target as HTMLInputElement).value || null)"
                class="w-full rounded border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none"
                placeholder="หมายเหตุ"
              />
            </td>
            <td class="px-3 py-2 text-center">
              <button
                type="button"
                @click="removeItem(index)"
                class="text-red-500 hover:text-red-700 text-sm"
                title="ลบรายการ"
              >
                ✕
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mt-3 flex items-center justify-between">
      <button
        type="button"
        @click="addItem"
        class="rounded border border-dashed border-gray-400 px-3 py-1.5 text-sm text-gray-600 hover:border-blue-500 hover:text-blue-600"
      >
        + เพิ่มรายการ
      </button>
      <div class="text-sm font-medium text-gray-700">
        ยอดรวม:
        <span class="text-base ml-1">{{ totalAmount.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} บาท</span>
      </div>
    </div>
  </div>
</template>

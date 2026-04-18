<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'
import { useFiscalYearStore } from '@/stores/fiscalYears'
import { useOrganizationStore } from '@/stores/organizations'
import ItemEditor from '@/components/ItemEditor.vue'
import type { ItemRow } from '@/components/ItemEditor.vue'

const router = useRouter()
const store = useBudgetRequestStore()
const fyStore = useFiscalYearStore()
const orgStore = useOrganizationStore()

const requestTitle = ref('')
const fiscalYear = ref<number>(0)
const orgId = ref<number | null>(null)
const items = ref<ItemRow[]>([
  { item_name: '', quantity: '0', unit_price: '0', remark: null, category_item_id: null },
])
const errorMsg = ref('')
const loading = ref(false)

onMounted(async () => {
  await Promise.all([fyStore.fetchList(), orgStore.fetchList()])
  const current = fyStore.fiscalYears.find((fy) => fy.is_current)
  if (current) fiscalYear.value = current.year
  else if (fyStore.fiscalYears.length > 0) fiscalYear.value = fyStore.fiscalYears[0].year
})

const canSave = computed(() =>
  requestTitle.value.trim() !== '' && items.value.some((i) => i.item_name.trim() !== ''),
)

async function saveDraft() {
  const result = await doCreate()
  if (result?.id) {
    router.push(`/requests/${result.id}`)
  }
}

async function saveAndSubmit() {
  const result = await doCreate()
  if (result?.id) {
    loading.value = true
    try {
      const submitResult = await store.submit(result.id)
      if (submitResult.ok) {
        router.push(`/requests/${result.id}`)
      } else {
        errorMsg.value = submitResult.error ?? 'ไม่สามารถส่งอนุมัติได้'
      }
    } finally {
      loading.value = false
    }
  }
}

async function doCreate(): Promise<{ id?: number } | null> {
  errorMsg.value = ''
  loading.value = true

  try {
    const validItems = items.value.filter((i) => i.item_name.trim() !== '')
    if (validItems.length === 0) {
      errorMsg.value = 'ต้องมีรายการอย่างน้อย 1 รายการ'
      return null
    }

    const result = await store.create({
      request_title: requestTitle.value.trim(),
      fiscal_year: fiscalYear.value,
      org_id: orgId.value,
      items: validItems,
    })

    if (!result.ok) {
      errorMsg.value = result.error ?? 'เกิดข้อผิดพลาด'
      return null
    }

    return { id: result.id }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">สร้างคำของบประมาณ</h1>
      <router-link to="/requests" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; กลับ
      </router-link>
    </div>

    <div v-if="errorMsg" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700" role="alert">
      {{ errorMsg }}
    </div>

    <div class="space-y-6 rounded-lg bg-white p-6 shadow">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อคำขอ *</label>
          <input
            v-model="requestTitle"
            type="text"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            placeholder="เช่น คำของบประมาณเดือนตุลาคม 2569"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">ปีงบประมาณ</label>
          <select
            v-model.number="fiscalYear"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option v-for="fy in fyStore.fiscalYears" :key="fy.id" :value="fy.year">
              {{ fy.year }}{{ fy.is_current ? ' (ปีปัจจุบัน)' : '' }}
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน</label>
          <select
            v-model="orgId"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option :value="null">-- เลือกหน่วยงาน --</option>
            <option v-for="org in orgStore.organizations" :key="org.id" :value="org.id">
              {{ org.name_th }}
            </option>
          </select>
        </div>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">รายการงบประมาณ</label>
        <ItemEditor v-model="items" />
      </div>

      <div class="flex gap-3 border-t pt-4">
        <button
          @click="saveDraft"
          :disabled="loading || !canSave"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
        >
          {{ loading ? 'กำลังบันทึก...' : 'บันทึกร่าง' }}
        </button>
        <button
          @click="saveAndSubmit"
          :disabled="loading || !canSave"
          class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'กำลังส่ง...' : 'ส่งอนุมัติ' }}
        </button>
      </div>
    </div>
  </div>
</template>
